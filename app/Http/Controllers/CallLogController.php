<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use App\CallLog;
use App\Campaign;
use App\TemplateHeader;

use App;
use PDF;
use Validator;

class CallLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // dd($request->input());

        $campaigns = Campaign::select('id', 'unique_key', 'name')->whereNull('deleted_at')->get();
        $callLogs = array();
        $selectedCampaign = [];

        $validator = Validator::make($request->input(), [
            'campaign' => 'nullable|numeric|min:1|max:' . Campaign::max('id'),
            'startdate' => 'nullable|date_format:d/m/Y',
            'enddate' => 'nullable|date_format:d/m/Y',
        ]);
        if ($validator->fails()) return back();

        $startdate  = $request->startdate ? $request->startdate : null;
        $enddate    = $request->enddate ? $request->enddate : null;
        $callLogs   = [];
        $rowNumber = 0;

        if (isset($request->campaign) && $request->campaign >= 1) {
            $selectedCampaign = Campaign::select(
                    'campaigns.id AS camp_id', 'campaigns.template_id AS camp_templ_id', 'campaigns.reference_table AS camp_ref_table',
                    'template_headers.name AS tmpl_header_name', 'template_headers.column_type AS tmpl_col_type',
                    'template_headers.is_mandatory AS templ_is_mandatory', 'template_headers.is_unique AS templ_is_unique',
                    'template_headers.is_voice AS templ_is_voice', 'template_headers.voice_position AS templ_voice_position'
                )
                ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
                ->where('campaigns.id', $request->campaign)
                ->whereNull('campaigns.deleted_at')
                ->get();
            // dd($selectedCampaign);

            if ($selectedCampaign->count() > 0) {
                $tableReference = strtolower($selectedCampaign[0]->camp_ref_table);
                $tableReferenceCallLogs = $tableReference . '_call_logs';
                $mandatoryColumns = [];

                foreach ($selectedCampaign AS $keyHeader => $valHeader) {
                    if ($valHeader->templ_is_mandatory || ($valHeader->tmpl_col_type === 'handphone')) {
                        $mandatoryColumns[] = $tableReference . '.' . strtolower(preg_replace('/\W+/i', '_', $valHeader->tmpl_header_name));
                    }
                }
                // dd($mandatoryColumns);

                $callLogs = DB::table($tableReferenceCallLogs)
                    ->select($tableReferenceCallLogs . '.*')
                    ->selectRaw(implode(', ', $mandatoryColumns))
                    ->leftJoin($tableReference, $tableReferenceCallLogs . '.contact_id', '=', $tableReference . '.id')
                    ->orderBy($tableReferenceCallLogs . '.id', 'DESC');

                if ($startdate) {
                    $f_startdate = date('Y-m-d', strtotime(str_replace('/', '-', $startdate)));
                    $f_enddate = date('Y-m-d', strtotime(str_replace('/', '-', $enddate)));
                    $callLogs->whereRaw('DATE(' . $tableReferenceCallLogs . '.call_dial) BETWEEN ? AND ?', [$f_startdate, $f_enddate]);
                }

                // dd($callLogs->toSql());
                $callLogs = $callLogs->paginate(15);
                $callLogs->appends([
                    '_token' => $request->_token,
                    'startdate' => $request->startdate,
                    'enddate' => $request->enddate,
                    'campaign' => $request->campaign,
                ]);
                // dd($callLogs);

                $rowNumber = $callLogs->firstItem();
            }
        }
        // else {
        // }
        
        return view('calllogs.index', array(
            'row_number' => $rowNumber,
            'campaigns' => $campaigns,
            'selectedCampaign' => $selectedCampaign,
            'calllogs' => $callLogs,
            'startdate' => $startdate,
            'enddate' => $enddate
        ));
    }

    public function exportData(Request $request)
    {
        $input = $request->all();
        // dd($input);

        $validator = Validator::make($input, [
            'export_campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'export_startdate'=>'sometimes|required',
            'export_enddate'=>'sometimes|required',
        ]);
        if ($validator->fails()) {
            return back();
        }

        $campaignId = $request->export_campaign ? $request->export_campaign : null;
        
        if ($campaignId) {
            $startdate  = $request->startdate ? $request->startdate : null;
            $enddate    = $request->enddate ? $request->enddate : null;
            $campaignCallLogs = $this->getCampaignCallLogs($campaignId, $startdate, $enddate);
            $filename = 'REPORT_CALL_LOGS_' . strtoupper($campaignCallLogs['campaign'][0]->camp_name) . '_' . time();

            Excel::create($filename, function($excel) use($campaignCallLogs, $startdate, $enddate, $campaignId) {
                $excel->sheet('Sheet1', function($sheet) use($campaignCallLogs, $startdate, $enddate, $campaignId) {
                    // ---
                    // --- report title
                    // ---
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue('REPORT CALL LOGS');
                    });

                    $subtitle = '';
                    if($startdate && $enddate){
                        $subtitle .= 'Date Range : '.$startdate.' - '.$enddate;
                    }
                    if($campaignId){
                        $subtitle .= $startdate ? ' | ': '';
                        $subtitle .= 'Campaign : '.$campaignCallLogs['campaign'][0]->camp_name;
                    }
                    $sheet->cell('A2', function($cell) use($subtitle) {
                        $cell->setValue($subtitle);
                    });
    
                    // ---
                    // --- template/header titles
                    // ---
                    $tempHeaders = [];
                    foreach($campaignCallLogs['campaign'] AS $keyHeader => $valHeader) {
                        $tempHeaders[] = strtoupper($valHeader->th_name);
                    }
                    $tempHeaders[] = 'CALL_CONNECT';
                    $tempHeaders[] = 'CALL_DISCONNECT';
                    $tempHeaders[] = 'CALL_DURATION';
                    $tempHeaders[] = 'CALL_RESPONSE';
                    $sheet->row(4, $tempHeaders);
                    array_splice($tempHeaders, -4);
    
                    // ---
                    // --- populate call-log data
                    // ---
                    $tempRowContent = [];
                    $excelRowNumber = 5;
                    $columnName = '';
                    foreach ($campaignCallLogs['call_logs']->chunk(300) AS $rows) {
                        // foreach($campaignCallLogs['call_logs'] as $row){
                        foreach($rows as $row){
                            $tempRowContent = [];

                            foreach ($tempHeaders AS $keyHeader => $valHeader) {
                                $columnName = strtolower(preg_replace('/\W+/i', '_', $valHeader));
                                $tempRowContent[] = strtoupper($row->$columnName);
                            }
    
                            $tempRowContent[] = $row->call_connect ? date('H:i:s', strtotime($row->call_connect)) : '';
                            $tempRowContent[] = $row->call_disconnect ? date('H:i:s', strtotime($row->call_disconnect)) : '';
                            $tempRowContent[] = $row->call_duration > 0 ? App\Helpers\Helpers::secondsToHms($row->call_duration) : '';
                            $tempRowContent[] = strtoupper($row->call_response);
    
                            $sheet->row($excelRowNumber, $tempRowContent);
                            $excelRowNumber++;
                        }
                    }
                });
            })->download('xlsx');
        }
    }

    public function recording(Request $request)
    {
        $audio = $request->audio;
        
        $checkpath  = '/var/spool/asterisk/monitor/'.$audio;            
        if(file_exists($checkpath)){
            $filepath = $checkpath;
        }else{
            $filepath = '/home/asterisk-recording/'.$audio;
        }

        header("Content-Transfer-Encoding: binary"); 
        header("Content-Type: audio/wav");
        header('Content-Disposition:: inline; filename="'.$audio.'"');
        header('Content-length: '.filesize($filepath));
        header('Cache-Control: no-cache');
        header('Accept-Ranges: bytes');
        readfile($filepath);
    }

    private function getCampaignCallLogs($cammpaignId, $startDate=null, $endDate=null)
    {
        $callLogs = [];

        $theCampaign = Campaign::select(
                'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.template_id AS camp_templ_id', 'campaigns.reference_table AS camp_ref_table',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_col_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
            ->where('campaigns.id', $cammpaignId)
            ->whereNull('campaigns.deleted_at')
            ->get();
        // dd($theCampaign);

        if ($theCampaign->count() > 0) {
            $tableReference = strtolower($theCampaign[0]->camp_ref_table);
            $tableReferenceCallLogs = $tableReference . '_call_logs';
            $mandatoryColumns = [];

            foreach ($theCampaign AS $keyHeader => $valHeader) {
                $mandatoryColumns[] = strtolower($tableReference . '.' . preg_replace('/W+/i', '_', $valHeader->th_name));
            }
            // dd($mandatoryColumns);

            $callLogs = DB::table($tableReferenceCallLogs)
                ->select($tableReferenceCallLogs . '.*')
                ->selectRaw(implode(', ', $mandatoryColumns))
                ->leftJoin($tableReference, $tableReferenceCallLogs . '.contact_id', '=', $tableReference . '.id')
                ->orderBy($tableReferenceCallLogs . '.id', 'ASC');

            if($startDate){
                $f_startdate = date('Y-m-d', strtotime(str_replace('/', '-', $startDate)));
                $f_enddate = date('Y-m-d', strtotime(str_replace('/', '-', $endDate)));
                $callLogs->whereRaw('DATE(' . $tableReferenceCallLogs . '.call_dial) BETWEEN ? AND ?', [$f_startdate, $f_enddate]);
            }

            // dd($callLogs->toSql());
            $callLogs = $callLogs->get();
            // dd($callLogs);
        }

        return array(
            'campaign' => $theCampaign,
            'call_logs' => $callLogs,
        );
    }

    /*
    public function exportData(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'export_startdate'=>'sometimes|required',
            'export_enddate'=>'sometimes|required',
        ]);

        if ($validator->fails()) {
            return back();
        }

        $campaign = null;
        $reportHeaders = CustomReportHeaders::whereNull('deleted_at')->get();

        $query = CallLog::leftJoin('contacts','call_logs.contact_id','=','contacts.id')
                        ->select('contacts.id AS contact_id', 'contacts.account_id', 'contacts.phone', 'call_logs.call_dial', 'call_logs.call_connect', 'call_logs.call_disconnect', 'call_logs.call_duration', 'call_logs.call_response', 'call_logs.call_recording');
                        

        if($request->export_startdate && $request->export_enddate){
            $startdate = date('Y-m-d', strtotime(str_replace('/', '-', $request->export_startdate)));
            $enddate = date('Y-m-d', strtotime(str_replace('/', '-', $request->export_enddate)));
            $query = $query->whereRaw('DATE(call_logs.call_dial) BETWEEN ? AND ?', [$startdate, $enddate]);
        }

        if($request->export_campaign){
            $query = $query->where('contacts.campaign_id', $request->export_campaign);
            $campaign = Campaign::where('id', $request->export_campaign)->first();
        }

        $callLogs = $query->orderBy('call_logs.id','ASC')->get();

        $filename = 'report-call-logs-'.time();
       
        // excel
        Excel::load(storage_path('app/public/files/Report_Call_Logs.xlsx'), function($file) use($request, $campaign, $callLogs) {
            $sheet = $file->setActiveSheetIndex(0);
            $subtitle = null;
            if($request->export_startdate && $request->export_enddate){
                $subtitle .= 'Date Range : '.$request->export_startdate.' - '.$request->export_enddate;
            }

            if($request->export_campaign){
                $subtitle .= $request->export_startdate ? ' | ': '';
                $subtitle .= 'Campaign : '.$campaign->name;
            }
            
            $sheet->setCellValue('A2', $subtitle);

            if ($reportHeaders->count() > 0) {
                foreach($reportHeaders AS $keyHeader => $valHeader) {
                    $sheet->setCellValueByColumnAndRow(9, 4, $valHeader->name);
                }
            }

            $excelRowNumber = 5;
            foreach ($callLogs->chunk(300) AS $rows) {
                foreach($callLogs as $row){
                    $sheet->setCellValue('A' . $excelRowNumber, date('d/m/Y', strtotime($row->call_dial)));
                    $sheet->setCellValue('B' . $excelRowNumber, $row->account_id);
                    $sheet->setCellValue('C' . $excelRowNumber, $row->phone);
                    $sheet->setCellValue('D' . $excelRowNumber, date('H:i:s', strtotime($row->call_dial)));
                    $sheet->setCellValue('E' . $excelRowNumber, $row->call_connect ? date('H:i:s', strtotime($row->call_connect)) : '');
                    $sheet->setCellValue('F' . $excelRowNumber, $row->call_disconnect ? date('H:i:s', strtotime($row->call_disconnect)) : '');
                    $sheet->setCellValue('G' . $excelRowNumber, $row->call_duration > 0 ? App\Helpers\Helpers::secondsToHms($row->call_duration) : '');
                    $sheet->setCellValue('H' . $excelRowNumber, $row->call_response);
                    $excelRowNumber++;
                }
            }

        })->download('xlsx');

    }
    */

    /*
    public function index(Request $request)
    {
        $campaigns = Campaign::select('id', 'unique_key', 'name')->get();
        $campaignMaxId = Campaign::select('id')->orderBy('id', 'DESC')->first();
        $callLogs = array();

        $validator = Validator::make(['campaign' => $request->campaign], [
            'campaign' => 'nullable|numeric|min:1|max:' . $campaignMaxId->id
        ]);
        if ($validator->fails()) return back();

        $callLogs = CallLog::select(
            'contacts.id AS contact_id', 'contacts.account_id', 'contacts.phone',
                'call_logs.call_dial', 'call_logs.call_connect', 'call_logs.call_disconnect', 'call_logs.call_duration', 'call_logs.call_response', 'call_logs.call_recording'
            )
            ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
            ->orderBy('call_logs.id', 'DESC');

        if ($request->campaign) {
            $callLogs->where('contacts.campaign_id', '=', $request->campaign);
        }

        $startdate  = $request->startdate ? $request->startdate : null;
        $enddate    = $request->enddate ? $request->enddate : null;

        if($startdate){
            $f_startdate = date('Y-m-d', strtotime(str_replace('/', '-', $startdate)));
            $f_enddate = date('Y-m-d', strtotime(str_replace('/', '-', $enddate)));

            $callLogs->whereRaw('DATE(call_logs.call_dial) BETWEEN ? AND ?', [$f_startdate, $f_enddate]);
        }

        $callLogs = $callLogs->paginate(15);

        return view('calllogs.index', array(
            'campaigns' => $campaigns,
            'selectedCampaign' => (int) $request->campaign,
            'calllogs' => $callLogs,
            'startdate' =>$startdate,
            'enddate' => $enddate
        ));
    }
    */

    /*
    public function exportData(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'export_type' => 'required|string|min:3|max:5',
            'campaign' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return back();
        }

        $data = array();
        $contacts = array();
        $campaign = Campaign::select('campaigns.*', 'users.name AS created_by')
            ->where('campaigns.id', '=', $request->campaign)
            ->leftJoin('users', 'campaigns.created_by', '=', 'users.id')
            ->first();

        if ($campaign) {
            switch($campaign->status) {
                case 0: $campaign->status = 'ready'; break;
                case 1: $campaign->status = 'running'; break;
                case 2: $campaign->status = 'paused'; break;
                case 3: $campaign->status = 'finished'; break;
                default: break;
            }

            $campaign->started = Contact::select('call_dial')->min('call_dial');
            $campaign->finished = '-';
            $campaign->total_calls = 0;
            $campaign->success = 0;
            $campaign->failed = 0;
            $campaign->progress = 0;
            $tempCalledNumber = [];
            
            $callLogs = CallLog::select(
                    'call_logs.call_dial AS CALL_DIAL',
                    'call_logs.call_connect AS CALL_CONNECT',
                    'call_logs.call_disconnect AS CALL_DISCONNECT',
                    'call_logs.call_duration AS CALL_DURATION',
                    'call_logs.call_response AS CALL_RESPONSE',
                    'call_logs.call_recording AS CALL_RECORDING',
                    'contacts.id AS contact_id', 'contacts.phone',
                    'contacts.account_id AS CONTACT_ACCOUNT_ID',
                    'contacts.name AS CONTACT_NAME'
                )
                ->rightJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
                ->where('contacts.campaign_id', '=', $campaign->id)
                ->orderBy('contacts.name', 'ASC')
                ->get();
            // dd($contacts);

            foreach ($callLogs AS $keyCallLog => $valueCallLog) {
                if (!empty($valueCallLog->CALL_DIAL)) {
                    $campaign->total_calls++;

                    if (!array_key_exists($valueCallLog->phone, $tempCalledNumber)) {
                        $tempCalledNumber[$valueCallLog->phone] = true;
                    }
                }

                switch ($valueCallLog->CALL_RESPONSE) {
                    case 'answered': $campaign->success++; break;
                    case 'failed': $campaign->failed++; break;
                    default: break;
                }

                unset($callLogs[$keyCallLog]->contact_id, $callLogs[$keyCallLog]->phone);
            }
            // dd($callLogs);

            $campaign->progress = number_format((count($tempCalledNumber) / $campaign->total_data) * 100, 2, '.', ',.');

            $data['campaign'] = $campaign;
            $data['contacts'] = $callLogs;
            $data['header'] = true;
            // dd($data);

            $fileName = 'CALL_LOGS-'
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

                $excelDownload = storage_path('app/public/files/Report_Call_Logs.xlsx');

                Excel::load($excelDownload, function($file) use($campaign, $callLogs, $excelDownload) {
                    $sheet = $file->setActiveSheetIndex(0);
                    $sheet->setCellValue('A2', $campaign->name);
                    $sheet->setCellValue('E2', $campaign->started);
                    $sheet->setCellValue('A5', $campaign->total_data);
                    $sheet->setCellValue('E5', $campaign->finished);
                    $sheet->setCellValue('A8', ucwords($campaign->status));
                    $sheet->setCellValue('E8', $campaign->total_calls);
                    $sheet->setCellValue('A11', $campaign->created_at);
                    $sheet->setCellValue('E11', $campaign->success);
                    $sheet->setCellValue('A14', $campaign->progress);
                    $sheet->setCellValue('E14', $campaign->failed);

                    $excelRowNumber = 17;
                    foreach ($callLogs AS $keyCallLog => $valueCallLog) {
                        $sheet->setCellValue('A' . $excelRowNumber, $valueCallLog['CONTACT_ACCOUNT_ID']);
                        $sheet->setCellValue('B' . $excelRowNumber, $valueCallLog['CONTACT_NAME']);
                        $sheet->setCellValue('C' . $excelRowNumber, $valueCallLog['CALL_DIAL']);
                        $sheet->setCellValue('D' . $excelRowNumber, $valueCallLog['CALL_CONNECT']);
                        $sheet->setCellValue('E' . $excelRowNumber, $valueCallLog['CALL_DISCONNECT']);
                        $sheet->setCellValue('F' . $excelRowNumber, $valueCallLog['CALL_DURATION']);
                        $sheet->setCellValue('G' . $excelRowNumber, $valueCallLog['CALL_RESPONSE']);
                        $sheet->setCellValue('H' . $excelRowNumber, $valueCallLog['CALL_RECORDING']);
                        $excelRowNumber++;
                    }
                })->download('xlsx');
            }
        }
        else {
            return back();
        }
    }
    */
}
