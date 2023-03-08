<?php

namespace App\Http\Controllers;

// use Anouar\Fpdf\Facades\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\CallLog;
use App\Campaign;
use App\Contact;
use Maatwebsite\Excel\Facades\Excel;

use App;
use PDF;

use App\Helpers\Helpers;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $campaigns = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.created_at'
            )
            ->selectRaw('
                users.name AS created_by,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS dialed_contacts
            ')
            ->leftjoin('users', 'campaigns.created_by', '=', 'users.id')
            ->paginate(15);

        $rowNumber = $campaigns->firstItem();

        return view('campaign.index', [
            'campaigns' => $campaigns,
            'row_number' => $rowNumber,
        ]);
    }

    public function create(Request $request)
    {
        return view('campaign.create');
    }

    public function show(Request $request, $id)
    {
        $campaign = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.created_at'
            )
            ->selectRaw("
                SUM(contacts.total_calls) AS total_calls, 
                (SELECT MIN(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS started,
                (SELECT MAX(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS finished,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'answered') AS success,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'failed') AS failed,
                (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS dialed_contacts
            ")
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->where('campaigns.id', '=', $id)
            ->first();

        $contacts = Contact::where('contacts.campaign_id', '=', $id)
            ->paginate(15);

        $rowNumber = $contacts->firstItem();

        $data = array(
            'row_number' => $rowNumber,
            'campaign' => $campaign,
            'contacts' => $contacts,
        );

        return view('campaign.show', $data);
    }

    public function edit(Request $request, $id)
    {
        $data = array(
            'row_number' => 0,
            'campaign' => array(),
            'contacts' => array(),
        );

        $campaignData = Campaign::select('campaigns.id', 'campaigns.name')
            ->selectRaw('
                COUNT(contacts.call_dial) AS dialed_contacts
            ')
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->whereNotNull('contacts.call_dial')
            ->find($id);

        if ($campaignData) {
            $contacts = Contact::where('contacts.campaign_id', '=', $campaignData->id)
                ->paginate(15);

            $data['row_number'] = $contacts->firstItem();
            $data['campaign'] = $campaignData;
            $data['contacts'] = $contacts;
        }

        return view('campaign.edit', $data);
    }

    public function delete(Request $request, $id)
    {
        $campaign = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.created_at'
            )
            ->selectRaw("
                SUM(contacts.total_calls) AS total_calls, 
                (SELECT MIN(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS started,
                (SELECT MAX(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS finished,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'answered') AS success,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'failed') AS failed,
                (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS dialed_contacts
            ")
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->find($id);

        $contacts = Contact::where('campaign_id', '=', $id)->paginate(15);
        $rowNumber = $contacts->firstItem();

        $data = array(
            'campaign' => $campaign,
            'contacts' => $contacts,
            'row_number' => $rowNumber,
        );
        // dd($data);

        return view('campaign.delete', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'input_campaign_rows' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (isset($request->input_campaign_rows)) {
            $dataRows = json_decode($request->input_campaign_rows); // dd($dataRows);

            if (count($dataRows) > 0) {
                $newContacts = [];
                $existingContacts = [];
                $todayDateTime = Carbon::now();

                $campaignCreate = Campaign::create(array(
                    'unique_key' => $todayDateTime->getTimestamp(),
                    'name' => $request->name,
                    'created_by' => Auth::user()->id,
                ));

                list($newContacts, $existingContacts) = $this->saveValidContacts($campaignCreate->id, $dataRows);

                $campaignCreate->total_data += count($newContacts);
                $campaignCreate->save();

                if (empty($existingContacts)) {
                    return redirect()->route('campaigns');
                }
                else {
                    return back()->with([
                        'name' => $campaignCreate->name,
                        'key' => $campaignCreate->unique_key,
                        'saved_contacts' => json_encode($newContacts),
                        'failed_contacts' => json_encode($existingContacts)
                    ]);
                }
            }
            else {
                return back();
            }
        }
        else {
            return back();
        }
    }
    
    public function update(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'rows' => 'nullable|string',
            'action' => 'nullable|string|max:8',
            'campaign' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $campaign = Campaign::
            where(function($q) {
                $q->where('status', '=', 0)
                  ->orWhere('status', '=', 2);
            })
            ->find($request->campaign);

        if ($campaign) {
            $countCallLogs = CallLog::where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->count('call_logs.contact_id');

            if ($countCallLogs == 0) {
                $existingContacts = [];
                $dataRows = json_decode($request->rows); // dd($dataRows);
                $newContacts = [];
                $existingContacts = [];
    
                if ($request->action && (count($dataRows) > 0)) {
                    if ($request->action === 'replace') {
                        Contact::where('campaign_id', '=', $campaign->id)
                            ->delete();
                        $campaign->total_data = 0;
                    }
    
                    list($newContacts, $existingContacts) = $this->saveValidContacts($campaign->id, $dataRows);
                    $campaign->total_data += count($newContacts);
                }

                $campaign->name = $request->name;
                $campaign->save();

                if (empty($existingContacts)) {
                    return redirect()->route('campaigns');
                }
                else {
                    return back()->with([
                        'name' => $campaign->name,
                        'key' => $campaign->unique_key,
                        'saved_contacts' => json_encode($newContacts),
                        'failed_contacts' => json_encode($existingContacts)
                    ]);
                }
            }
            else {
                return back()->with([
                    'name' => $campaign->name,
                    'key' => $campaign->unique_key,
                    'already_running' => 'This campaign already run',
                ]);
            }
        }
        else {
            return back();
        }
    }

    public function destroy(Request $request)
    {
        $campaign = Campaign::find($request->campaign);

        if ($campaign) {
            // Contact::where('campaign_id', '=', $campaign->id)->delete();
            $campaign->delete();
        }

        return redirect()->route('campaigns');
    }

    public function updateStartStop(Request $request)
    {
        $returnedResponse = array(
            'code' => 500,
            'message' => 'Server Failed',
            'count' => 0,
            'data' => array(),
        );

        $validator = Validator::make($request->input(), [
            'campaign' => 'required|numeric',
            'currstatus' => 'required|numeric|min:0|max:3',
        ]);

        if ($validator->fails()) {
            $returnedResponse['message'] = $validator->errors();
        }
        else {
            $campaignData = Campaign::select('campaigns.id', 'campaigns.status')
                ->find($request->campaign);

            if ($campaignData != null) {
                $newStatus = $campaignData->status;

                switch ($campaignData->status) {
                    default: break;
                    case 0: $newStatus = 1; break; // ready to running
                    case 1: $newStatus = 2; break; // running to paused
                    case 2: $newStatus = 1; break; // paused to running
                }

                $campaignData->status = $newStatus;
                $campaignData->save();

                $returnedResponse['code'] = 200;
                $returnedResponse['message'] = 'OK';
                $returnedResponse['count'] = 1;
            }
            else {
                $returnedResponse['code'] = 404;
                $returnedResponse['message'] = 'Campaign not found.';
            }
        }

        return response()->json($returnedResponse, $returnedResponse['code']);
    }

    public function exportData(Request $request)
    {
        $campaign = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.created_at'
            )
            ->selectRaw("
                SUM(contacts.total_calls) AS total_calls, 
                (SELECT MIN(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS started,
                (SELECT MAX(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id) AS finished,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'answered') AS success,
                (SELECT COUNT(contacts.call_response) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_response = 'failed') AS failed,
                (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS dialed_contacts
            ")
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->where('campaigns.id', '=', $request->campaign)
            ->first();
            
        $contacts = array();
        $data = array(
            'campaign' => $campaign,
            'contacts' => $contacts,
        );

        if ($campaign) {
            $contacts = Contact::select(
                    'contacts.id',
                    'contacts.account_id',
                    'contacts.name',
                    'contacts.phone',
                    'contacts.bill_date',
                    'contacts.due_date',
                    'contacts.total_calls',
                    'contacts.nominal',
                    'contacts.call_dial',
                    'contacts.call_response'
                )
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->get();
                
            $data['contacts'] = $contacts;
            // dd($data);

            $fileName = 'IVR_BLAST-'
                . $campaign->unique_key
                . '-' . strtoupper(Str::replaceFirst(' ', '_', $campaign->name))
                . '-' . Carbon::now('Asia/Jakarta')->format('Ymd_His');
            
            if ($request->export_type === 'pdf') {
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadView('campaign.show_pdf', $data);
                return $pdf->download($fileName . '.pdf');
            }
            else if ($request->export_type === 'excel') {
                $excelDownload = storage_path('app/public/files/Report_IVR_Blast.xlsx');
                
                Excel::load($excelDownload, function($file) use($campaign, $contacts, $excelDownload) {
                    $progress = number_format(($campaign->dialed_contacts / $campaign->total_data) * 100, 2, '.', ',');

                    $sheet = $file->setActiveSheetIndex(0);
                    $sheet->setCellValue('A2', $campaign->name);
                    $sheet->setCellValue('E2', $campaign->started ? date('d/m/Y - H:i', strtotime($campaign->started)) : '-');
                    $sheet->setCellValue('A5', $campaign->total_data);
                    $sheet->setCellValue('E5', ($campaign->finished != '-') ? date('d/m/Y - H:i', strtotime($campaign->finished)) : '-');
                    $sheet->setCellValue('A8', $campaign->status);
                    $sheet->setCellValue('E8', $campaign->total_calls);
                    $sheet->setCellValue('A11', date('d/m/Y - H:i', strtotime($campaign->created_at)));
                    $sheet->setCellValue('E11', $campaign->success);
                    $sheet->setCellValue('A14', $progress);
                    $sheet->setCellValue('E14', $campaign->failed);

                    $excelRowNumber = 17;
                    foreach ($contacts->chunk(100) as $chunks) {
                        foreach ($chunks as $valueContact) {
                            $sheet->setCellValue('A' . $excelRowNumber, (string) $valueContact['account_id']);
                            $sheet->setCellValue('B' . $excelRowNumber, $valueContact['name']);
                            $sheet->setCellValue('C' . $excelRowNumber, $valueContact['phone']);
                            $sheet->setCellValue('D' . $excelRowNumber, $valueContact['bill_date']);
                            $sheet->setCellValue('E' . $excelRowNumber, $valueContact['due_date']);
                            $sheet->setCellValue('F' . $excelRowNumber, $valueContact['nominal']);
                            $sheet->setCellValue('G' . $excelRowNumber, $valueContact['total_calls']);
                            $sheet->setCellValue('H' . $excelRowNumber, $valueContact['call_dial']);
                            $sheet->setCellValue('I' . $excelRowNumber, strtoupper($valueContact['call_response']));
                            $excelRowNumber++;
                        }
                    }

                })->download('xlsx');
            }
        }
        else {
            return back();
        }
    }

    public function downloadTemplate()
    {
        $fileTemplate = public_path('files/Template_IVR_Blast.xlsx');
        return response()->download($fileTemplate);
    }
    
    public function exportFailedContacts(Request $request)
    {
        $validate = Validator::make($request->input(), [
            'input_key' => 'required|numeric',
            'input_name' => 'required|string|min:5|max:30',
            'input_failed_contacts' => 'required|string'
        ]);

        if ($validate->fails()) {
            return back();
        }

        $failedContacts = json_decode($request->input_failed_contacts);
        $contacts = [];

        foreach($failedContacts AS $keyFailedContact => $valueFailedContact) {
            $contacts[] = array(
                'ACCOUNT_ID' => $valueFailedContact->account_id,
                'NAME' => $valueFailedContact->name,
                'PHONE' => $valueFailedContact->phone,
                'BILL_DATE' => $valueFailedContact->bill_date,
                'DUE_DATE' => $valueFailedContact->due_date,
                'NOMINAL' => $valueFailedContact->nominal,
                'FAILED_REASON' => $valueFailedContact->failed,
            );
        }

        $fileName = 'IVR_BLAST_FAILED_UPLOAD-'
        . $request->input_key
        . '-' . strtoupper(Str::replaceFirst(' ', '_', $request->input_name))
        . '-' . Carbon::now('Asia/Jakarta')->format('Ymd_His');

        Excel::create($fileName, function($excel) use($contacts) {
            $excel->sheet('contacts', function($sheet) use($contacts) {
                $sheet->fromArray($contacts);
            });
        })->export('xlsx');
    }

    private function saveValidContacts($campaignId, $dataRows)
    {
        $newContacts = [];
        $existingContacts = [];
        $tempContact = [];

        // -- connection with sip
        $sip = DB::connection('sip')
                ->table('sip')
                ->selectRaw('DISTINCT(id) as extension, data as callerid')
                ->where('keyword', 'callerid')
                ->where('id', 'like', env('SIP_PREFIX_EXT').'%')
                ->orderBy(DB::raw('RAND()'))
                ->get();


        foreach ($dataRows AS $keyDataRows => $valueDataRows) {
            $tempContact['campaign_id'] = $campaignId;
            $tempContact['account_id'] = $valueDataRows->account_id;

            $isExists = Contact::where('campaign_id', '=', $tempContact['campaign_id'])
                ->where('account_id', '=', trim($tempContact['account_id']))
                ->exists();

            if (!$isExists) {
                $tempContact['name'] = $valueDataRows->name;
                $tempContact['phone'] = trim(preg_replace('/\D/', '', $valueDataRows->phone));
                $tempContact['bill_date'] = $valueDataRows->bill_date;
                $tempContact['due_date'] = $valueDataRows->due_date;
                $tempContact['nominal'] = $valueDataRows->nominal;

                // -- connection with sip
                $rand = rand(0,($sip->count()-1));
                $tempContact['extension']   = $sip[$rand]->extension;
                $tempContact['callerid']    = $sip[$rand]->callerid;
                $tempContact['voice']       = Helpers::generateVoice([
                    'bill_date' => $valueDataRows->bill_date,
                    'due_date' => $valueDataRows->due_date,
                    'nominal' => $valueDataRows->nominal
                ]);


                $first8Position = stripos($tempContact['phone'], '8', 0);

                if ($first8Position === false) {
                    $tempContact['failed'] = 'Phone number error';
                    $existingContacts[] = $tempContact;
                }
                else {
                    if ($first8Position > 5) {
                        $tempContact['failed'] = 'Phone number error';
                        $existingContacts[] = $tempContact;
                    }
                    else {
                        $tempContact['phone'] = '0' . substr($tempContact['phone'], $first8Position);
                        if ((strlen($tempContact['phone']) >= 10) && (strlen($tempContact['phone']) <= 15)) {
                            $isPhoneExists = Contact::where('campaign_id', '=', $tempContact['campaign_id'])
                                ->where('phone', '=', trim($tempContact['phone']))
                                ->exists();

                            if (!$isPhoneExists) {
                                unset($tempContact['failed']);
                                Contact::insert($tempContact);
                                $newContacts[] = $tempContact;
                            }
                            else {
                                $tempContact['failed'] = 'Phone number exists';
                                $existingContacts[] = $tempContact;
                            }
                        }
                        else {
                            $tempContact['failed'] = 'Phone format error';
                            $existingContacts[] = $tempContact;
                        }
                    }
                }
            }
            else {
                $tempContact['failed'] = 'Account-ID already exists';
                $existingContacts[] = $tempContact;
            }
        }

        return array($newContacts, $existingContacts);
    }
}
