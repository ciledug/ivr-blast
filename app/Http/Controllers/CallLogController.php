<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Helpers\Helpers;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

use App\CallLog;
use App\Campaign;

class CallLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function callLogs(Request $request)
    {
        $callLogs = $this->getCallLogs($request);
        return view('calllogs.index', $callLogs);
    }

    public function exportData(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'export_campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'export_startdate'=>'sometimes|required',
            'export_enddate'=>'sometimes|required',
        ]);
        if ($validator->fails()) {
            return back();
        }

        $callLogs = $this->getCallLogs($request, true);
        // dd($callLogs);
        
        if ($callLogs['selectedCampaign'] !== null && $callLogs['selectedCampaign']->count() > 0) {
            $filename = 'REPORT_CALL_LOGS_'
                . strtoupper(preg_replace('/\W+/i', '_', $callLogs['selectedCampaign'][0]->camp_name)) . '_'
                . Carbon::now('Asia/Jakarta')->format('dmY_His');

            Excel::create($filename, function($excel) use($callLogs) {
                $excel->sheet('Sheet1', function($sheet) use($callLogs) {
                    // ---
                    // --- report title
                    // ---
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue('REPORT CALL LOGS');
                    });

                    $subtitle = '';
                    if ($callLogs['startdate'] && $callLogs['enddate']) {
                        $subtitle .= 'Date Range : ' . $callLogs['startdate'] . ' - ' . $callLogs['enddate'];
                    }

                    if ($callLogs['selectedCampaign']->count() > 0){
                        $subtitle .= $callLogs['startdate'] ? ' | ': '';
                        $subtitle .= 'Campaign : ' . $callLogs['selectedCampaign'][0]->camp_name;
                    }

                    $sheet->cell('A2', function($cell) use($subtitle) {
                        $cell->setValue($subtitle);
                    });

                    // ---
                    // --- template/header titles
                    // ---
                    $tempHeaders = [];
                    foreach($callLogs['selectedCampaign'] AS $keyHeader => $valHeader) {
                        $tempHeaders[] = strtoupper(preg_replace('/\W+/i', '_', $valHeader->tmpl_header_name));
                    }
                    $tempHeaders[] = 'CALL_DIAL';
                    $tempHeaders[] = 'CALL_CONNECT';
                    $tempHeaders[] = 'CALL_DISCONNECT';
                    $tempHeaders[] = 'CALL_DURATION';
                    $tempHeaders[] = 'CALL_RESPONSE';
                    $sheet->row(4, $tempHeaders);
                    array_splice($tempHeaders, -5);
    
                    // ---
                    // --- populate call-log data
                    // ---
                    $tempRowContent = [];
                    $excelRowNumber = 5;
                    $columnName = '';
                    foreach ($callLogs['calllogs']->chunk(300) AS $rows) {
                        // foreach($callLogs['calllogs'] as $row){
                        foreach($rows as $row){
                            $tempRowContent = [];

                            foreach ($tempHeaders AS $keyHeader => $valHeader) {
                                $columnName = strtolower(preg_replace('/\W+/i', '_', $valHeader));
                                $tempRowContent[] = strtoupper($row->$columnName);
                            }

                            $tempRowContent[] = $row->cl_call_dial ? date('d-m-Y', strtotime($row->cl_call_dial)) : '';
                            $tempRowContent[] = $row->cl_call_connect ? date('H:i:s', strtotime($row->cl_call_connect)) : '';
                            $tempRowContent[] = $row->cl_call_disconnect ? date('H:i:s', strtotime($row->cl_call_disconnect)) : '';
                            $tempRowContent[] = $row->cl_call_duration > 0 ? Helpers::secondsToHms($row->cl_call_duration) : '';
                            $tempRowContent[] = strtoupper($row->cl_call_response);
    
                            $sheet->row($excelRowNumber, $tempRowContent);
                            $excelRowNumber++;
                        }
                    }
                });

            })->download('xlsx');
        }
    }

    private function getCallLogs(Request $request, $isForExport=false)
    {
        // dd($request->input());

        $validator = Validator::make($request->input(), [
            'campaign' => 'nullable|numeric|min:1|max:' . Campaign::max('id'),
            'startdate' => 'nullable|date_format:d/m/Y',
            'enddate' => 'nullable|date_format:d/m/Y',
            'export_campaign' => 'nullable|numeric|min:1|max:' . Campaign::max('id'),
            'export_startdate'=>'nullable|date_format:d/m/Y',
            'export_enddate'=>'nullable|date_format:d/m/Y',
        ]);
        if ($validator->fails()) return back();

        $selectedCampaign = null;
        $callLogs = [];
        $startdate = null;
        $enddate = null;
        $rowNumber = 0;

        $campaigns = Campaign::select('id', 'unique_key', 'name')->whereNull('deleted_at')->get();

        if (
            (isset($request->campaign) && $request->campaign >= 1)
            || (isset($request->export_campaign) && $request->export_campaign >= 1)
        ) {
            $requestedCampaign = 0;
            if (isset($request->campaign)) $requestedCampaign = $request->campaign;
            else if (isset($request->export_campaign)) $requestedCampaign = $request->export_campaign;

            $selectedCampaign = Campaign::select(
                    'campaigns.id AS camp_id', 'campaigns.unique_key AS camp_unique_key', 'campaigns.name AS camp_name', 'campaigns.template_id AS camp_templ_id',
                    'campaigns.reference_table AS camp_ref_table',
                    'template_headers.name AS tmpl_header_name', 'template_headers.column_type AS tmpl_col_type',
                    'template_headers.is_mandatory AS templ_is_mandatory', 'template_headers.is_unique AS templ_is_unique',
                    'template_headers.is_voice AS templ_is_voice', 'template_headers.voice_position AS templ_voice_position'
                )
                ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
                ->where('campaigns.id', $requestedCampaign)
                ->whereNull('campaigns.deleted_at')
                ->get();
            // dd($selectedCampaign);

            if ($selectedCampaign->count() > 0) {
                if ($request->startdate !== null) $startdate = $request->startdate;
                else if ($request->export_startdate !== null) $startdate = $request->export_startdate;
        
                if ($request->enddate !== null) $enddate = $request->enddate;
                else if ($request->export_enddate !== null) $enddate = $request->export_enddate;

                $tableReference = $selectedCampaign[0]->camp_ref_table;
                $mandatoryColumns = [];

                foreach ($selectedCampaign AS $keyHeader => $valHeader) {
                    $mandatoryColumns[] = $tableReference . '.' . strtolower(preg_replace('/\W+/i', '_', $valHeader->tmpl_header_name));
                }
                // dd($mandatoryColumns);

                $callLogs = CallLog::select(
                        'call_logs.id AS cl_id', 'call_logs.campaign_id AS cl_camp_id', 'call_logs.contact_id AS cl_contact_id',
                        'call_logs.call_dial AS cl_call_dial', 'call_logs.call_connect AS cl_call_connect', 'call_logs.call_disconnect AS cl_call_disconnect',
                        'call_logs.call_duration AS cl_call_duration', 'call_logs.call_recording AS cl_call_recording', 'call_logs.call_response AS cl_call_response'
                    )
                    ->selectRaw(implode(', ', $mandatoryColumns))
                    ->leftJoin($tableReference, 'call_logs.contact_id', '=', $tableReference . '.contact_id')
                    ->where('call_logs.campaign_id', $selectedCampaign[0]->camp_id)
                    ->orderBy('call_logs.call_dial', 'DESC');

                if ($startdate) {
                    $f_startdate = date('Y-m-d', strtotime(str_replace('/', '-', $startdate)));
                    $f_enddate = date('Y-m-d', strtotime(str_replace('/', '-', $enddate)));
                    $callLogs->whereRaw('DATE(call_logs.call_dial) BETWEEN ? AND ?', [$f_startdate, $f_enddate]);
                }
                // dd($callLogs->toSql());

                if ($isForExport) $callLogs = $callLogs->get();
                else {
                    $callLogs = $callLogs->paginate(15);
                    $callLogs->appends([
                        '_token' => $request->_token,
                        'startdate' => $request->startdate,
                        'enddate' => $request->enddate,
                        'campaign' => $request->campaign,
                    ]);
                    $rowNumber = $callLogs->firstItem();
                }
                // dd($callLogs);
            }
        }

        return [
            'campaigns' => $campaigns,
            'selectedCampaign' => $selectedCampaign,
            'calllogs' => $callLogs,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'row_number' => $rowNumber,
        ];
    }
}
