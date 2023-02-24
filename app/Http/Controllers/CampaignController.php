<?php

namespace App\Http\Controllers;

use Anouar\Fpdf\Facades\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Exports\CampaignExport;
use App\CallLog;
use App\Campaign;
use App\Contact;
use Maatwebsite\Excel\Facades\Excel;

use App;
use PDF;

class CampaignController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('campaign.index');
    }

    public function create(Request $request)
    {
        return view('campaign.create');
    }

    /*
    public function show(Request $request, $campaign=null)
    {
        $data = array(
            'campaign' => $this->getCampaignData($request, $campaign),
        );

        return view('campaign.show', $data);
    }

    public function edit(Request $request, $campaign=null)
    {
        $data = array();

        $campaignData = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $campaign))
            ->first();

        if ($campaignData) {
            $data['campaign'] = $campaignData;
        }

        return view('campaign.edit', $data);
    }
    */

    public function show(Request $request, $campaign=null)
    {
        $data = array(
            'campaign' => $this->getCampaignData($request, $campaign),
        );
        $data['contacts'] = $this->getContactListCommon($request, $campaign);
        // dd($data);

        return view('campaign.show', $data);
    }

    public function edit(Request $request, $campaign=null)
    {
        $data = array();
        $campaignData = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $campaign))
            ->first();

        if ($campaignData) {
            $data['campaign'] = $campaignData;
            $data['contacts'] = $this->getContactListCommon($request, $campaign);
        }

        return view('campaign.edit', $data);
    }

    public function delete(Request $request, $campaign=null)
    {
        $data = array(
            'campaign' => $this->getCampaignData($request, $campaign),
        );

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
                    return redirect()->route('campaign');
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

        $campaign = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->where(function($q) {
                $q->where('status', '=', 0)
                    ->orWhere('status', '=', 2);
            })
            ->first();

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
                    return redirect()->route('campaign');
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
        $campaign = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->first();

        if ($campaign) {
            Contact::where('campaign_id', '=', $campaign->id)
                ->delete();

            $campaign->delete();
        }

        return redirect()->route('campaign');
    }

    public function getCampaignList(Request $request)
    {
        $returnedCode = 500;
        $campaignList = array();

        $query = Campaign::select(DB::raw('
                campaigns.id AS campaign_id, campaigns.unique_key, campaigns.name, campaigns.total_data, campaigns.total_calls, campaigns.created_by,
                IF (campaigns.status = 0, "Ready", IF (campaigns.status = 1, "Running", IF (campaigns.status = 2, "Paused", "Finished"))) AS status,
                DATE_FORMAT(campaigns.created_at, "%d/%m/%Y - %H:%i") AS created,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial
            '))
            ->whereNull('campaigns.deleted_at')
            ->orderBy('campaigns.created_at', 'desc');
        $campaignData = $query->get();

        if ($campaignData) {
            $returnedCode = 200;

            if (count($campaignData) > 0) {
                $seq = 1;
                $tempFailedCalls = 0;
                $tempStartDate = '';
                $tempFinishDate = '';
                $tempProgress = 0;

                foreach ($campaignData AS $keyCampaignData => $valueCampaignData) {
                    $tempProgress = 0;
                    if ($valueCampaignData->call_dial > 0) {
                        $tempProgress = ($valueCampaignData->call_dial / $valueCampaignData->total_data) * 100;
                    }

                    $tempFailedCalls = CallLog::select('call_logs.contact_id')
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->where('call_logs.call_response', '=', 3)
                        ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                        ->orderBy('call_logs.created_at', 'DESC')
                        ->count('call_logs.contact_id');

                    $tempStartDate = Contact::select(DB::raw('
                            DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i") AS started
                        '))
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                        ->first();

                    $tempFinishDate = Contact::select(DB::raw('
                            DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i") AS finished
                        '))
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                        ->first();

                    $campaignList[] = array(
                        'seq' => $seq,
                        'created' => $valueCampaignData->created,
                        'name' => $valueCampaignData->name,
                        'started' => $tempStartDate->started,
                        'finished' => $tempFinishDate->finished,
                        'total' => $valueCampaignData->total_data,
                        'total_calls' => $valueCampaignData->total_calls,
                        'call_dial' => $valueCampaignData->call_dial,
                        'call_connect' => $valueCampaignData->call_connect,
                        'call_failed' => $tempFailedCalls,
                        'status' => $valueCampaignData->status,
                        'created_by' => $valueCampaignData->created_by,
                        'key' => $valueCampaignData->unique_key,
                        'progress' => $tempProgress,
                    );
                    $seq++;
                }
            }
        }

        $returnedResponse = array(
            'code' => $returnedCode,
            'data' => $campaignList
        );

        return response()->json($returnedResponse);
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
            'currstatus' => 'required|string|min:5|max:10',
            'startstop' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $returnedResponse['message'] = $validator->errors();
        }
        else {
            $campaignData = Campaign::where('unique_key', '=', $request->campaign)
                ->first();

            if ($campaignData != null) {
                if ($request->input('startstop') != null) {
                    $newStatus = $campaignData->status;

                    switch ($request->currstatus) {
                        case 'ready': $newStatus = 1; break; // ready to running
                        case 'running': $newStatus = 2; break; // running to paused
                        case 'paused': $newStatus = 1; break; // paused to running
                        default: break;
                    }

                    $campaignData->status = $newStatus;
                    $campaignData->save();

                    $returnedResponse['code'] = 200;
                    $returnedResponse['message'] = 'OK';
                    $returnedResponse['count'] = 1;
                }
            }
            else {
                $returnedResponse['code'] = 404;
                $returnedResponse['message'] = 'Campaign not found.';
            }
        }

        return response()->json($returnedResponse);
    }

    public function exportData(Request $request)
    {
        $data = array();
        $campaign = $this->getCampaignData($request, null);
        $contacts = array();

        if ($campaign) {
            $campaign->total_calls = 0;
            
            $contacts = Contact::select(DB::raw("
                    campaigns.name AS CAMPAIGN_NAME,
                    contacts.id,
                    contacts.account_id AS ACCOUNT_ID,
                    contacts.name AS CONTACT_NAME,
                    contacts.phone AS CONTACT_PHONE,
                    contacts.bill_date AS BILL_DATE,
                    contacts.due_date AS DUE_DATE,
                    contacts.nominal AS NOMINAL,
                    IF (contacts.total_calls IS NULL, 0, contacts.total_calls) AS TOTAL_CALLS
                "))
                ->leftJoin('campaigns', 'contacts.campaign_id', '=', 'campaigns.id')
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->get();

            foreach ($contacts AS $keyContact => $valueContact) {
                $contacts[$keyContact]['CALL_DATE'] = '-';
                $contacts[$keyContact]['CALL_RESPONSE'] = '-';
                $tempCallLog = CallLog::select('call_dial', 'call_response')
                    ->where('contact_id', '=', $valueContact->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($tempCallLog) {
                    $contacts[$keyContact]['CALL_DATE'] = $tempCallLog->call_dial;
                    $contacts[$keyContact]['CALL_RESPONSE'] = ucwords($tempCallLog->call_response);
                }

                $campaign->total_calls += $contacts[$keyContact]['TOTAL_CALLS'];
                unset($contacts[$keyContact]['id']);
            }

            $data['campaign'] = $campaign;
            $data['contacts'] = $contacts;
            $data['header'] = true;
            // dd($data);

            $fileName = 'IVR_BLAST-'
                . $campaign->unique_key
                . '-' . strtoupper(Str::replaceFirst(' ', '_', $campaign->name))
                . '-' . Carbon::now('Asia/Jakarta')->format('Ymd_His');
            
            if ($request->export_type === 'pdf') {
                $data['header'] = false;
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadView('campaign.show_pdf', $data);
                return $pdf->download($fileName . '.pdf');
            }
            else if ($request->export_type === 'excel') {
                // -- download plain excel ...
                // Excel::create($fileName, function($excel) use($contacts) {
                //     $excel->sheet('contacts', function($sheet) use($contacts) {
                //         $sheet->fromArray($contacts);
                //     });
                // })->export('xlsx');

                // -- download via template and rename the downloaded file name
                // $excelDownload = storage_path('app/public/files/' . $fileName . '.xlsx');
                // copy(
                //     storage_path('app/public/files/template_report_campaign_ivr_blast.xlsx'),
                //     $excelDownload
                // );

                $excelDownload = storage_path('app/public/files/Report_IVR_Blast.xlsx');

                Excel::load($excelDownload, function($file) use($campaign, $contacts, $excelDownload) {
                    $sheet = $file->setActiveSheetIndex(0);
                    $sheet->setCellValue('A2', $campaign->name);
                    $sheet->setCellValue('E2', $campaign->started);
                    $sheet->setCellValue('A5', $campaign->total_data);
                    $sheet->setCellValue('E5', $campaign->finished);
                    $sheet->setCellValue('A8', $campaign->status);
                    $sheet->setCellValue('E8', $campaign->total_calls);
                    $sheet->setCellValue('A11', $campaign->created_at);
                    $sheet->setCellValue('E11', $campaign->success);
                    $sheet->setCellValue('A14', $campaign->progress);
                    $sheet->setCellValue('E14', $campaign->failed);

                    $excelRowNumber = 17;
                    foreach ($contacts AS $keyContact => $valueContact) {
                        $sheet->setCellValue('A' . $excelRowNumber, (string) $valueContact['ACCOUNT_ID']);
                        $sheet->setCellValue('B' . $excelRowNumber, $valueContact['CONTACT_NAME']);
                        $sheet->setCellValue('C' . $excelRowNumber, $valueContact['CONTACT_PHONE']);
                        $sheet->setCellValue('D' . $excelRowNumber, $valueContact['BILL_DATE']);
                        $sheet->setCellValue('E' . $excelRowNumber, $valueContact['DUE_DATE']);
                        $sheet->setCellValue('F' . $excelRowNumber, $valueContact['NOMINAL']);
                        $sheet->setCellValue('G' . $excelRowNumber, $valueContact['TOTAL_CALLS']);
                        $sheet->setCellValue('H' . $excelRowNumber, $valueContact['CALL_DATE']);
                        $sheet->setCellValue('I' . $excelRowNumber, $valueContact['CALL_RESPONSE']);
                        $excelRowNumber++;
                    }
                })->download('xlsx');
            }
        }
        else {
            return back();
        }
    }

    public function getCampaignListAjax(Request $request)
    {
        $ORDERED_COLUMNS = ['created', 'created', 'name', 'total', 'status', 'created_by'];
        $ORDERED_BY = ['desc', 'asc'];
        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? $request->start : 0;
        $LENGTH = is_numeric($request->length) ? $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $campaignList = array();

        $query = Campaign::select(DB::raw('
                campaigns.id AS campaign_id,
                campaigns.unique_key,
                campaigns.name,
                campaigns.total_data,
                campaigns.status AS status,
                users.name AS created_by,
                DATE_FORMAT(campaigns.created_at, "%d/%m/%Y - %H:%i") AS created,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial
            '))
            ->leftJoin('users', 'campaigns.created_by', '=', 'users.id');

        if (!empty($SEARCH_VALUE)) {
            $query->where(function($q) use($SEARCH_VALUE) {
                $q->where('campaigns.name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orWhere('campaigns.created_by', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orwhere('campaigns.created_at', 'LIKE', '%' . $SEARCH_VALUE . '%');
            });
        }

        if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
            $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
        }

        // DB::enableQueryLog();
        $filteredData = $query->get();
        $campaignData = $query->offset($START)->limit($LENGTH)->get();
        // dd(DB::getQueryLog());
        // dd($campaignData);

        if ($campaignData) {
            if (count($campaignData) > 0) {
                $seq = 1;
                $tempFailedCalls = 0;
                $tempStartDate = '';
                $tempFinishDate = '';
                $tempProgress = 0;

                foreach ($campaignData AS $keyCampaignData => $valueCampaignData) {
                    $tempProgress = 0;
                    if ($valueCampaignData->call_dial > 0) {
                        $tempProgress = ($valueCampaignData->call_dial / $valueCampaignData->total_data) * 100;
                    }

                    $tempFailedCalls = CallLog::select('call_logs.contact_id')
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->where('call_logs.call_response', '=', 'failed')
                        ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                        ->orderBy('call_logs.id', 'DESC')
                        ->count('call_logs.contact_id');

                    $tempStartDate = Contact::select(DB::raw('
                            DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i") AS started
                        '))
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                        ->first();

                    $tempFinishDate = Contact::select(DB::raw('
                            DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i") AS finished
                        '))
                        ->where('contacts.campaign_id', '=', $valueCampaignData->campaign_id)
                        ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                        ->first();

                    switch ($valueCampaignData->status) {
                        case 0: $valueCampaignData->status = 'Ready'; break;
                        case 1: $valueCampaignData->status = 'Running'; break;
                        case 2: $valueCampaignData->status = 'Paused'; break;
                        case 3: $valueCampaignData->status = 'Finished'; break;
                        default: break;
                    }

                    $campaignList[] = array(
                        'seq' => $seq,
                        'created' => $valueCampaignData->created,
                        'name' => $valueCampaignData->name,
                        'started' => $tempStartDate->started,
                        'finished' => $tempFinishDate->finished,
                        'total' => $valueCampaignData->total_data,
                        'total_calls' => $valueCampaignData->total_calls,
                        'call_dial' => $valueCampaignData->call_dial,
                        'call_connect' => $valueCampaignData->call_connect,
                        'call_failed' => $tempFailedCalls,
                        'status' => $valueCampaignData->status,
                        'created_by' => $valueCampaignData->created_by,
                        'key' => $valueCampaignData->unique_key,
                        'progress' => $tempProgress,
                    );
                    
                    $seq++;
                }
            }
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => Campaign::all()->count(),
            'recordsFiltered' => $filteredData->count(),
            'data' => $campaignList
        );

        return response()->json($returnedResponse);
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

    private function getCampaignData($request, $campaign)
    {
        $campaignData = Campaign::select(DB::raw("
                campaigns.id,
                campaigns.name AS name,
                campaigns.unique_key AS unique_key,
                campaigns.total_data AS total_data,
                campaigns.created_at,
                campaigns.status
            "))
            ->where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->first();

        if ($campaignData) {
            $campaignData->total_calls = 0;
            $tempContactIds = Contact::select('id')
                ->where('campaign_id', '=', $campaignData->id)
                ->get();
            foreach ($tempContactIds AS $keyContactIds => $valueContactIds) {
                $tempCount = CallLog::select('id')
                    ->where('contact_id', '=', $valueContactIds->id)
                    ->count('id');
                $campaignData->total_calls += $tempCount;
            }

            $campaignData->started = CallLog::leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->min('call_logs.call_dial');

            $campaignData->finished = CallLog::leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->max('call_logs.call_disconnect');

            $campaignData->success = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 'answered')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.id', 'DESC')
                ->count('call_logs.contact_id');

            $campaignData->failed = CallLog::select('call_logs.contact_id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->where('call_logs.call_response', '=', 'failed')
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.id', 'DESC')
                ->count('call_logs.contact_id');

            $calledContacts = CallLog::select('call_logs.id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('contacts', 'contacts.id', '=', 'call_logs.contact_id')
                // ->orderBy('call_logs.id', 'desc')
                ->groupBy('call_logs.contact_id')
                ->get();
                
            $campaignData->progress = number_format(($calledContacts->count() / $campaignData->total_data) * 100, 2, ',', '.');
            if ($campaignData->progress < 100) {
                $campaignData->finished = '-';
            }

            switch ($campaignData->status) {
                case 0: $campaignData->status = 'Ready'; break;
                case 1: $campaignData->status = 'Running'; break;
                case 2: $campaignData->status = 'Paused'; break;
                case 3: $campaignData->status = 'Finished'; break;
            }
        }

        // dd($campaignData);
        return $campaignData;
    }

    private function saveValidContacts($campaignId, $dataRows)
    {
        $newContacts = [];
        $existingContacts = [];
        $tempContact = [];

        foreach ($dataRows AS $keyDataRows => $valueDataRows) {
            $tempContact['campaign_id'] = $campaignId;
            $tempContact['account_id'] = $valueDataRows->account_id;
            $tempContact['name'] = $valueDataRows->name;
            $tempContact['phone'] = $valueDataRows->phone;
            $tempContact['bill_date'] = $valueDataRows->bill_date;
            $tempContact['due_date'] = $valueDataRows->due_date;
            $tempContact['nominal'] = $valueDataRows->nominal;

            $isExists = Contact::where('campaign_id', '=', $tempContact['campaign_id'])
                ->where('account_id', '=', trim($tempContact['account_id']))
                ->exists();

            if (!$isExists) {
                $tempContact['phone'] = trim(preg_replace('/\D/', '', $tempContact['phone']));
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

    private function getContactListCommon($request, $campaign) {
        $ORDERED_COLUMNS = ['account_id', 'name', 'phone', 'bill_date', 'due_date', 'total_calls', 'nominal', 'call_dial', 'call_response'];
        $ORDERED_BY = ['desc', 'asc'];

        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? (int) $request->start : 0;
        $LENGTH = is_numeric($request->length) ? (int) $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $campaign = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $campaign))->first();
        // dd($campaign);

        $recordsTotalQuery = 0;
        $contactList = [];

        if ($campaign) {
            $query = Contact::select(DB::raw('
                    id,
                    account_id,
                    contacts.name AS name,
                    phone,
                    DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                    DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                    IF (total_calls IS NULL, 0, total_calls) AS total_calls,
                    FORMAT(nominal, 0) AS nominal
                '))
                ->where('campaign_id', '=', $campaign->id);

            if (!empty($SEARCH_VALUE)) {
                $query->where(function($q) use($SEARCH_VALUE) {
                    $q->where('contacts.account_id', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orWhere('contacts.name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('contacts.phone', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('bill_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('due_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('nominal', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_dial', 'LIKE', '%' . $SEARCH_VALUE . '%');
                });
            }

            if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
                $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
            }
            $filteredData = $query->get();
            // $contactList = $query->offset($START)->limit($LENGTH)->get();
            $contactList = $query->paginate(15);

            foreach ($contactList AS $keyContact => $valueContact) {
                $contactList[$keyContact]->call_dial = '-';
                $contactList[$keyContact]->call_response = '-';
                $tempCallLog = CallLog::select('call_dial', 'call_response')
                    ->where('contact_id', '=', $valueContact->id)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($tempCallLog) {
                    $contactList[$keyContact]->call_dial = $tempCallLog->call_dial;
                    $contactList[$keyContact]->call_response = ucwords($tempCallLog->call_response);
                }
            }
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => Contact::where('contacts.campaign_id', '=', $campaign->id)->count(),
            'recordsFiltered' => $filteredData->count(),
            'data' => $contactList
        );

        return $returnedResponse;
    }
}
