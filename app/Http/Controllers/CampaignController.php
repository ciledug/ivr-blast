<?php

namespace App\Http\Controllers;

use Anouar\Fpdf\Facades\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    //use Fpdf;
    
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
        $data = array();

        $campaignData = Campaign::select(DB::raw("
                campaigns.id,
                campaigns.name AS name,
                campaigns.unique_key AS unique_key,
                campaigns.total_data AS total_data,
                campaigns.total_calls AS total_calls,
                campaigns.created_at,
                IF(campaigns.status = 0, 'Ready', IF(campaigns.status = 1, 'Running', 'Finished')) AS status,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS call_dial,
                (SELECT COUNT(contacts.call_connect) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_connect IS NOT NULL) AS call_connect,
                (SELECT COUNT(contacts.call_duration) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_duration IS NOT NULL) AS call_duration
            "))
            ->where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->whereNull('deleted_at')
            ->first();

        if ($campaignData) {
            $tempProgress = 0;
            if ($campaignData->call_connect > 0) {
                $tempProgress = ($campaignData->call_connect / $campaignData->total_data) * 100;
            }
            $campaignData->progress = $tempProgress;

            $tempSuccessCalls = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 0)
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaignData->success = $tempSuccessCalls;

            $tempFailedCalls = CallLog::select('call_logs.contact_id')
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->where('call_logs.call_response', '=', 3)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaignData->failed = $tempFailedCalls;

            $tempStartDate = Contact::select(DB::raw('
                    IF (call_logs.call_dial IS NOT NULL, DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i"), \'0000-00-00 00:00:00\') AS started
                '))
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaignData->started = $tempStartDate->started;

            $tempFinishDate = Contact::select(DB::raw('
                    IF (call_logs.call_disconnect IS NOT NULL, DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i"), \'0000-00-00 00:00:00\') AS finished
                '))
                ->where('contacts.campaign_id', '=', $campaignData->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaignData->finished = $tempFinishDate->finished;

            $data['campaign'] = $campaignData;
        }

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
        $data = array();

        $campaignData = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $campaign))
            ->whereNull('deleted_at')
            ->first();

        if ($campaignData) {
            $data['campaign'] = $campaignData;
        }

        return view('campaign.delete', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'input_campaign_rows' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (isset($request->input_campaign_rows)) {
            $dataRows = json_decode($request->input_campaign_rows);
            // dd($dataRows);

            if (count($dataRows) > 0) {
                $todayDateTime = Carbon::now();
                $campaignUniqueKey = $todayDateTime->getTimestamp();

                $campaignCreate = Campaign::create(array(
                    'unique_key' => $campaignUniqueKey,
                    'name' => $request->input('name'),
                    'total_data' => count($dataRows),
                    'created_by' => Auth::user()->username,
                ));

                $tempContact = array();

                foreach ($dataRows AS $keyDataRows => $valueDataRows) {
                    $todayDateTime = Carbon::now();

                    $tempContact['campaign_id'] = $campaignCreate->id;
                    $tempContact['account_id'] = $valueDataRows->account_id;
                    $tempContact['name'] = $valueDataRows->name;
                    $tempContact['phone'] = $valueDataRows->phone;
                    $tempContact['bill_date'] = $valueDataRows->bill_date;
                    $tempContact['due_date'] = $valueDataRows->due_date;
                    $tempContact['nominal'] = $valueDataRows->nominal;

                    Contact::create($tempContact);
                }
            }
        }
        
        return redirect()->route('campaign');
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
            ->where('status', '=', '0')
            ->whereNull('deleted_at')
            ->first();

        if ($campaign) {
            $dataRows = json_decode($request->rows);
            // dd($dataRows);

            if ($request->action && (count($dataRows) > 0)) {
                $tempContact = array();
                $tempTotalData = count($dataRows);
                
                if ($request->action === 'replace') {
                    Contact::where('campaign_id', '=', $campaign->id)
                        ->whereNull('deleted_at')
                        ->delete();
                    $campaign->total_data = 0;
                }

                foreach ($dataRows AS $keyDataRows => $valueDataRows) {
                    $todayDateTime = Carbon::now();

                    $tempContact['campaign_id'] = $campaign->id;
                    $tempContact['account_id'] = $valueDataRows->account_id;
                    $tempContact['name'] = $valueDataRows->name;
                    $tempContact['phone'] = $valueDataRows->phone;
                    $tempContact['bill_date'] = $valueDataRows->bill_date;
                    $tempContact['due_date'] = $valueDataRows->due_date;
                    $tempContact['nominal'] = $valueDataRows->nominal;

                    Contact::create($tempContact);
                }

                $campaign->total_data += $tempTotalData;
            }

            $campaign->name = $request->name;
            $campaign->save();

            return redirect()->route('campaign');
        }
        else {
            return back();
        }
    }

    public function destroy(Request $request)
    {
        $campaign = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))
            ->whereNull('deleted_at')
            ->first();

        if ($campaign) {
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
                IF (campaigns.status = 0, "Ready", IF (campaigns.status = 1, "Running", "Finished")) AS status,
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
        $returnedCode = 500;
        $campaignList = array();

        $campaignData = Campaign::where('unique_key', $request->input('campaign'))
            ->whereNull('deleted_at')
            ->first();

        if ($campaignData != null) {
            if ($request->input('startStop') != null) {
                $campaignData->status = !$campaignData->status;
                $campaignData->save();
            }
            else {
                // 
            }

            $returnedCode = 200;
        }

        $returnedResponse = array(
            'code' => $returnedCode
        );

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
                IF(campaigns.status = 0, 'Ready', IF(campaigns.status = 1, 'Running', 'Finished')) AS status,
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
                ->where('call_logs.call_response', '=', 0)
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaign->success = $tempSuccessCalls;

            $tempFailedCalls = CallLog::select('call_logs.contact_id')
                ->where('call_logs.call_response', '=', 3)
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->orderBy('call_logs.created_at', 'DESC')
                ->count('call_logs.contact_id');
            $campaign->failed = $tempFailedCalls;

            $tempStartDate = Contact::select(DB::raw('
                    IF (call_logs.call_dial IS NOT NULL, DATE_FORMAT(MIN(call_logs.call_dial), "%d/%m/%Y %H:%i"), \'0000-00-00 00:00:00\') AS started
                '))
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->leftJoin('call_logs', 'contacts.id', '=', 'call_logs.contact_id')
                ->first();
            $campaign->started = $tempStartDate->started;

            $tempFinishDate = Contact::select(DB::raw('
                    IF (call_logs.call_disconnect IS NOT NULL, DATE_FORMAT(MAX(call_logs.call_disconnect), "%d/%m/%Y %H:%i"), \'0000-00-00 00:00:00\') AS finished
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
                    IF(contacts.call_response = 0, 'Answered',
                      IF(contacts.call_response = 1, 'No Answer',
                        IF(contacts.call_response = 2, 'Busy',
                          IF(contacts.call_response = 3, 'Failed', '')
                        )
                      )
                    ) AS CALL_RESPONSE
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
}
