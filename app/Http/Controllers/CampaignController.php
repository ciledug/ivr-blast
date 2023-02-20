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
            ->whereNull('deleted_at')
            ->first();

        if ($campaignData) {
            $data['campaign'] = $campaignData;
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
                    'created_by' => Auth::user()->username,
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
            ->where('total_calls', '=', '0')
            ->where(function($q) {
                $q->where('status', '=', 'ready')
                    ->orWhere('status', '=', 'paused');
            })
            ->first();

        if ($campaign) {
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
                IF (campaigns.status = 0, "Ready", IF (campaigns.status = 1, "Running", IF (campaigns.status = 2, "Finished", "Paused"))) AS status,
                DATE_FORMAT(campaigns.created_at, "%d/%m/%Y - %H:%i") AS created,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial,
                (SELECT COUNT(contacts.call_connect) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_connect IS NOT NULL) AS call_connect
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
            'currstatus' => 'required|string|min:5|max:9',
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
                        case 'ready': $newStatus = 'running'; break; // ready to running
                        case 'running': $newStatus = 'paused'; break; // running to paused
                        case 'paused': $newStatus = 'running'; break; // paused to running
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

        $campaign = Campaign::select(DB::raw("
                campaigns.id,
                campaigns.name AS name,
                campaigns.unique_key AS unique_key,
                campaigns.total_data AS total_data,
                campaigns.created_at,
                CONCAT(UCASE(LEFT(campaigns.status, 1)), SUBSTRING(campaigns.status, 2)) AS status,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial
            "))
            ->where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->first();
        // dd($campaign);

        if ($campaign) {
            $tempProgress = 0;
            // if ($campaign->call_dial > 0) {
            //     $tempProgress = ($campaign->call_dial / $campaign->total_data) * 100;
            // }
            $campaign->progress = $tempProgress;

            $tempSuccessCalls = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 'success')
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaign->success = $tempSuccessCalls;

            $tempFailedCalls = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 'failed')
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaign->failed = $tempFailedCalls;

            $tempStartDate = Contact::select(DB::raw('
                    IF (call_logs.call_dial IS NOT NULL, DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i"), \'-\') AS started
                '))
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaign->started = $tempStartDate->started;

            $tempFinishDate = Contact::select(DB::raw('
                    IF (call_logs.call_disconnect IS NOT NULL, DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i"), \'-\') AS finished
                '))
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaign->finished = $tempFinishDate->finished;
            
            $contacts = Contact::select(DB::raw("
                    campaigns.name AS CAMPAIGN_NAME,
                    contacts.account_id AS ACCOUNT_ID,
                    contacts.name AS CONTACT_NAME,
                    contacts.phone AS CONTACT_PHONE,
                    contacts.bill_date AS BILL_DATE,
                    contacts.due_date AS DUE_DATE,
                    contacts.nominal AS NOMINAL,
                    contacts.call_dial AS CALL_DIAL,
                    contacts.call_connect AS CALL_CONNECT,
                    contacts.call_disconnect AS CALL_DISCONNECT,
                    contacts.call_duration CALL_DURATION,
                    CONCAT(UCASE(LEFT(contacts.call_response, 1)), SUBSTRING(contacts.call_response, 2)) AS CALL_RESPONSE
                "))
                ->leftJoin('campaigns', 'contacts.campaign_id', '=', 'campaigns.id')
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->get();

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
                Excel::create($fileName, function($excel) use($contacts) {
                    $excel->sheet('contacts', function($sheet) use($contacts) {
                        $sheet->fromArray($contacts);
                    });
                })->export('xlsx');
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
                campaigns.id AS campaign_id, campaigns.unique_key, campaigns.name, campaigns.total_data, campaigns.total_calls, campaigns.created_by,
                CONCAT(UCASE(LEFT(campaigns.status, 1)), SUBSTRING(campaigns.status, 2)) AS status,
                DATE_FORMAT(campaigns.created_at, "%d/%m/%Y - %H:%i") AS created,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial,
                (SELECT COUNT(contacts.call_connect) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_connect IS NOT NULL) AS call_connect
            '))
            ->offset($START)
            ->limit($LENGTH);

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

        DB::enableQueryLog();
        $campaignData = $query->get();
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
            'draw' => $request->draw,
            'recordsTotal' => Campaign::all()->count(),
            'recordsFiltered' => count($campaignList),
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
                campaigns.total_calls AS total_calls,
                campaigns.created_at,
                CONCAT(UCASE(LEFT(campaigns.status, 1)), SUBSTRING(campaigns.status, 2)) AS status,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial,
                (SELECT COUNT(contacts.call_connect) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_connect IS NOT NULL) AS call_connect,
                (SELECT COUNT(contacts.call_duration) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_duration IS NOT NULL) AS call_duration
            "))
            ->where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->first();

        if ($campaignData) {
            $tempProgress = 0;
            if ($campaignData->call_connect > 0) {
                $tempProgress = ($campaignData->call_connect / $campaignData->total_data) * 100;
            }
            $campaignData->progress = $tempProgress;

            $tempSuccessCalls = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 'success')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaignData->success = $tempSuccessCalls;

            $tempFailedCalls = CallLog::select('call_logs.contact_id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->where('call_logs.call_response', '=', 'failed')
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaignData->failed = $tempFailedCalls;

            $tempStartDate = Contact::select(DB::raw('
                    IF (call_logs.call_dial IS NOT NULL, DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i"), \'-\') AS started
                '))
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaignData->started = $tempStartDate->started;

            $tempFinishDate = Contact::select(DB::raw('
                    IF (call_logs.call_disconnect IS NOT NULL, DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i"), \'-\') AS finished
                '))
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaignData->finished = $tempFinishDate->finished;
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
                                Contact::create($tempContact);
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
