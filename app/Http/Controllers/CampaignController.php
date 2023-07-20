<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use App\Helpers\Helpers;
use App\Campaign;
use App\Contact;
use App\Template;
use App\TemplateHeader;

use App;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campaigns = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.reference_table', 'campaigns.created_at',
                DB::raw('COUNT(contacts.call_dial) AS dialed_contacts'),
                'users.name AS created_by'
            )
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->leftjoin('users', 'campaigns.created_by', '=', 'users.id')
            // ->whereNotNull('contacts.call_dial')
            ->groupBy('campaigns.id')
            ->orderBy('campaigns.id', 'DESC')
            ->paginate(15);
        // dd($campaigns);

        return view('campaign.index', [
            'campaigns' => $campaigns,
            'row_number' => $campaigns->firstItem(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('campaign.create', array(
            'templates' => $this->getTemplates(),
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->input());

        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:50',
            'select_campaign_template' => 'required|numeric|min:1|max:' . Template::max('id'),
            // 'input_campaign_rows' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (isset($request->input_campaign_rows)) {
            $dataRows = json_decode($request->input_campaign_rows);
            // dd($dataRows);
            
            if (count($dataRows) > 0) {
                $todayDateTime = Carbon::now();
                $refTableHeaders = Template::select(
                        'templates.id AS templ_id', 'templates.reference_table AS templ_reference_table',
                        'template_headers.name AS th_name', 'template_headers.column_type AS th_column_type', 'template_headers.is_mandatory AS th_is_mandatory',
                        'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice',
                        'template_headers.voice_position as th_voice_position'
                    )
                    ->leftJoin('template_headers', 'templates.id', '=', 'template_headers.template_id')
                    ->where('templates.id', $request->select_campaign_template)
                    ->get();
                // dd($refTableHeaders);

                if ($refTableHeaders->count() > 0) {
                    // -- connection with sip
                    /*
                    $sip = DB::connection('sip')
                            ->table('sip')
                            ->selectRaw('DISTINCT(id) as extension, data as callerid')
                            ->where('keyword', 'callerid')
                            ->where('id', 'like', env('SIP_PREFIX_EXT').'%')
                            ->orderBy(DB::raw('RAND()'))
                            ->get();
                    $sipIdxCount = $sip->count() - 1;
                    $rand = 0;
                    */

                    // try {
                        $newCampaign = Campaign::create(array(
                            'unique_key' => $todayDateTime->getTimestamp(),
                            'name' => $request->name,
                            'created_by' => Auth::user()->id,
                            'template_id' => $refTableHeaders[0]->templ_id,
                            'reference_table' => $refTableHeaders[0]->templ_reference_table,
                        ));

                        $tempTotalData = 0;
                        $tempColumnName = '';
                        $tempPhoneNumber = '';
                        $tempDate = '';
                        $tempNominal = 0;
                        $sipExtension = 0;
                        $sipCallerId = '';

                        foreach ($dataRows AS $keyDataRow => $valDataRow) {
                            $refContact = [];

                            $newContact = Contact::create([
                                'campaign_id' => $newCampaign->id,
                                'created_at' => $todayDateTime->format('Y-m-d H:i:s'),
                                'updated_at' => $todayDateTime->format('Y-m-d H:i:s')
                            ]);

                            foreach ($refTableHeaders AS $keyHeader => $valHeader) {
                                $tempColumnName = strtolower(preg_replace('/\W+/i', '_', $valHeader->th_name));

                                if ($valHeader->th_column_type === 'numeric') {
                                    $tempNominal = $valDataRow->$tempColumnName;
                                }

                                if ($valHeader->th_column_type === 'handphone') {
                                    $tempPhoneNumber = $valDataRow->$tempColumnName;
                                }

                                if ($valHeader->th_column_type === 'date') {
                                    $tempDate = $valDataRow->$tempColumnName;
                                }

                                $refContact[$tempColumnName] = $valDataRow->$tempColumnName;
                                unset($valHeader, $keyHeader);
                            }

                            $refContact['campaign_id'] = $newCampaign->id;
                            $refContact['contact_id'] = $newContact->id;
                            DB::table($refTableHeaders[0]->templ_reference_table)->insert($refContact);

                            // -- connection with sip
                            // $rand           = rand(0, sipIdxCount);
                            $sipExtension   = isset($sip) ? $sip[$rand]->extension : null;
                            $sipCallerId    = isset($sip) ? $sip[$rand]->callerid : null;
                            $sipVoice       = Helpers::generateVoice([
                                'bill_date' => $tempDate,
                                'due_date' => $tempDate,
                                'nominal' => $tempNominal
                            ]);
                            
                            $newContact->phone = $tempPhoneNumber;
                            $newContact->nominal = $tempNominal;
                            $newContact->extension = $sipExtension;
                            $newContact->callerid = $sipCallerId;
                            $newContact->voice = $sipVoice;
                            $newContact->save();

                            $tempTotalData++;

                            unset($valDataRow, $keyDataRow, $refContact, $newContact);
                        }

                        $newCampaign->total_data = $tempTotalData;
                        $newCampaign->save();
                        // dd($newCampaign);

                        // if (count($existingContacts) <= 0) {
                        //     return redirect()->route('campaigns.index');
                        // }
                        // else {
                        //     return redirect()
                        //         ->route('campaigns.edit', ['id' => $newCampaign->id])
                        //         ->with([
                        //             'name' => $newCampaign->name,
                        //             'saved_contacts' => json_encode($newContacts),
                        //             'failed_contacts' => json_encode($existingContacts)
                        //         ]);
                        // }

                        return redirect()->route('campaigns.index');
                        
                    // } catch (\Illuminate\Database\QueryException $ex) {
                    //     // dd($ex);
                    //     $validator->errors()->add(
                    //         'campaign_referense_exists',
                    //         'Campaign Name with selected Campaign Template already exists.
                    //     ');
                    //     return back()->withErrors($validator)->withInput();
                    // }
                }
                else {
                    $validator->errors()->add('no_template_headers', 'Selected template doesn\'t exist.');
                    return back()->withErrors($validator)->withInput();
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = array(
            'campaign' => [],
            'campaign_contacts_info' => [],
            'row_number' => 0,
        );

        $campaign = Campaign::select(
                'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.total_data AS camp_total_data', 'campaigns.status AS camp_status',
                'campaigns.created_at AS camp_created_at', 'campaigns.reference_table AS camp_ref_table',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_column_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('template_headers', 'campaigns.template_id' ,'=', 'template_headers.template_id')
            ->where('campaigns.id', '=', $id)
            ->whereNull('campaigns.deleted_at')
            ->get();
        // dd($campaign);

        if ($campaign->count() > 0) {
            $maxDialTries = 3;
            $referenceTable = $campaign[0]->camp_ref_table;

            $refColumns = [];
            foreach($campaign AS $keyCampaign => $valCampaign) {
                $refColumns[] = $referenceTable . '.' . strtolower(preg_replace('/\W+/i', '_', $valCampaign->th_name));
            }
            // dd($refColumns);

            $campaignInfo = DB::table($referenceTable)
                ->selectRaw("
                    contacts.id AS cont_id, contacts.call_dial AS cont_call_dial, contacts.call_response AS cont_call_response,
                    contacts.total_calls AS cont_total_calls,"
                    . implode(', ', $refColumns) . ",
                    (SELECT SUM(contacts.total_calls) FROM contacts WHERE campaign_id = " . $campaign[0]->camp_id . ") AS sum_total_calls, 
                    (SELECT MIN(call_logs.call_dial) FROM call_logs WHERE campaign_id = " . $campaign[0]->camp_id . ") AS started,
                    (SELECT MAX(call_logs.call_dial) FROM call_logs WHERE campaign_id = " . $campaign[0]->camp_id . ") AS finished,
                    (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id=" . $campaign[0]->camp_id . " AND (contacts.total_calls=" . $maxDialTries . " OR contacts.call_response='answered')) AS completed_dials,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='answered' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS success,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='busy' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS busy,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='no_answer' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS no_answer,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='failed' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS failed,
                    (SELECT COUNT(call_logs.id) FROM call_logs WHERE call_logs.call_dial IS NOT NULL AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS dialed_contacts
                ")
                ->leftJoin('contacts', $referenceTable . '.contact_id', '=', 'contacts.id')
                ->where($referenceTable . '.campaign_id', $campaign[0]->camp_id);

            $data['campaign'] = $campaign;
            $data['campaign_contacts_info'] = $campaignInfo->paginate(15);
            $data['row_number'] = $data['campaign_contacts_info']->firstItem();
        }
        // dd($data);

        return view('campaign.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contacts = [];
        $campaignHeaders = Campaign::select(
                'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.total_data AS camp_total_data', 'campaigns.status AS camp_status',
                'campaigns.created_at AS camp_created_at', 'campaigns.reference_table AS camp_ref_table', 'campaigns.template_id AS camp_templ_id',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_column_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('template_headers', 'campaigns.template_id' ,'=', 'template_headers.template_id')
            ->where('campaigns.id', '=', $id)
            ->whereNull('campaigns.deleted_at')
            ->get();
        // dd($campaignHeaders);

        if ($campaignHeaders->count() > 0) {
            $refTable = $campaignHeaders[0]->camp_ref_table;
            $refColumns = [];

            foreach($campaignHeaders AS $keyCampaignHeaders => $valCampaignHeaders) {
                $refColumns[] = $refTable . '.' . strtolower($valCampaignHeaders->th_name);
            }

            $contacts = Contact::select(
                    'contacts.id', 'contacts.phone AS cont_phone', 'contacts.nominal AS cont_nominal', 'contacts.created_at AS cont_created_at'
                )
                ->selectRaw(implode(', ', $refColumns) . ', null AS errors')
                ->leftJoin($refTable, 'contacts.id', '=', $refTable . '.contact_id')
                ->where('contacts.campaign_id', $campaignHeaders[0]->camp_id)
                ->get();
        }

        return view('campaign.edit', array(
            'templates' => $this->getTemplates(),
            'campaign_headers' => $campaignHeaders,
            'contacts' => $contacts,
        ));
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
        // dd($request->input());

        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:50',
            'previous_campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'previous_template' => 'required|numeric|min:1|max:' . Template::max('id'),
            'selected_template' => 'required|numeric|min:1|max:' . Template::max('id'),

            'campaign_edit_action' => 'nullable|string',
            'input_campaign_rows' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $campaign = Campaign::find($request->previous_campaign);
        // dd($campaign);

        if ($campaign != null) {
            $template = Template::find($request->selected_template);
            // dd($template);

            if (
                $template
                && isset($request->input_campaign_rows)
                && !empty($request->input_campaign_rows)
            ) {
                // -- connection with sip
                // $sip = DB::connection('sip')
                //         ->table('sip')
                //         ->selectRaw('DISTINCT(id) as extension, data as callerid')
                //         ->where('keyword', 'callerid')
                //         ->where('id', 'like', env('SIP_PREFIX_EXT').'%')
                //         ->orderBy(DB::raw('RAND()'))
                //         ->get();
                // $sipIdxCount = $sip->count() - 1;
                // $rand = 0;
                
                $newCampaignRows = json_decode($request->input_campaign_rows);
                // dd($newCampaignRows);

                $tempReferenceTable = $template->reference_table; // dd($tempReferenceTable);
                $tempDateTime = '';
                $tempContactPhone = '';
                $tempContactNominal = 0;
                $tempTotalValidData = 0;

                if (strtolower($request->campaign_edit_action) === 'replace') {
                    Contact::where('campaign_id', $campaign->id)
                        ->delete();

                    DB::table($campaign->reference_table)
                        ->where($campaign->reference_table . '.campaign_id', $campaign->id)
                        ->delete();

                    $campaign->total_data = 0;
                    $campaign->template_id = $template->id;
                    $campaign->reference_table = $tempReferenceTable;
                }

                foreach ($newCampaignRows AS $keyCampaignRow => $valCampaignRow) {
                    $tempDateTime = Carbon::now('Asia/Jakarta');
                    $tempContactPhone = '';
                    $tempContactNominal = 0;
                    $tempContactDate = '';
                    $tempContact = null;
                    $sipExtension = null;
                    $sipCallerId = null;
                    $sipVoice = null;

                    foreach ($valCampaignRow->col_info AS $keyColInfo => $valColInfo) {
                        // dd($valData);
                        if ($valColInfo->type === 'handphone') {
                            $tempContactPhone = $valColInfo->value;
                        }
                        else if ($valColInfo->type === 'numeric') {
                            $tempContactNominal = $valColInfo->value;
                        }
                        else if ($valColInfo->type === 'date') {
                            $tempContactDate = $valColInfo->value;
                        }

                        unset($valColInfo, $keyColInfo);
                    }

                    try {
                        // -- connection with sip
                        // $rand           = rand(0, sipIdxCount);
                        // $sipExtension   = $sip[$rand]->extension;
                        // $sipCallerId    = $sip[$rand]->callerid;
                        $sipVoice       = Helpers::generateVoice([
                            'bill_date' => $tempContactDate,
                            'due_date' => $tempContactDate,
                            'nominal' => $tempContactNominal
                        ]);

                        $tempContact = Contact::create([
                            'campaign_id' => $campaign->id,
                            'phone' => $tempContactPhone,
                            'nominal' => $tempContactNominal,
                            'extension' => $sipExtension,
                            'callerid' => $sipCallerId,
                            'voice' => $sipVoice,
                            'created_at' => $tempDateTime->format('Y-m-d H:i:s'),
                            'updated_at' => $tempDateTime->format('Y-m-d H:i:s'),
                        ]);

                        unset($valCampaignRow->col_info, $valCampaignRow->errors, $valCampaignRow->value);
                        $valCampaignRow->campaign_id = $campaign->id;
                        $valCampaignRow->contact_id = $tempContact->id;
                        $valCampaignRow->created_at = $tempDateTime->format('Y-m-d H:i:s');
                        $valCampaignRow->updated_at = $tempDateTime->format('Y-m-d H:i:s');
                        // dd($valCampaignRow);

                        DB::table($tempReferenceTable)->insert((array) $valCampaignRow);
                        $tempTotalValidData++;
                    } catch (QueryException $ex) {
                        // dd($ex);
                    }

                    unset(
                        $sipVoice, $sipCallerId, $sipExtension,
                        $tempContact, $tempContactDate, $tempContactNominal, $tempContactPhone,
                        $tempDateTime, $valCampaignRow
                    );
                }

                $campaign->total_data += $tempTotalValidData;

                unset($tempReferenceTable, $tempDateTime, $tempContactPhone, $tempContactNominal, $tempTotalValidData);
            }
            // dd($refContent);

            $campaign->name = trim($request->name);
            $campaign->save();
        }

        // dd($campaign);
        unset($campaign);
        return redirect()->route('campaigns.index');
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

    public function updateStartStop(Request $request)
    {
        $returnedResponse = array(
            'code' => 500,
            'message' => 'Server Failed',
            'count' => 0,
            'data' => array(),
        );

        $validator = Validator::make($request->input(), [
            'campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'currstatus' => 'required|numeric|min:0|max:3',
        ]);

        if ($validator->fails()) {
            $returnedResponse['message'] = $validator->errors();
        }
        else {
            $campaignData = Campaign::select('campaigns.id', 'campaigns.status')
                ->find($request->campaign);

            if ($campaignData) {
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
        // dd($request->input());

        $contacts = array();
        $campaign = TemplateHeader::select(
                'template_headers.name AS th_name', 'template_headers.column_type AS th_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position',
                'campaigns.id AS camp_id', 'campaigns.unique_key AS camp_unique_key', 'campaigns.name AS camp_name', 'campaigns.total_data AS camp_total_data',
                'campaigns.status AS camp_status', 'campaigns.created_at AS camp_created_at', 'campaigns.reference_table AS camp_reference_table'
            )
            ->leftJoin('campaigns', 'template_headers.template_id', '=', 'campaigns.template_id')
            ->where('campaigns.id', '=', $request->campaign)
            ->whereNull('campaigns.deleted_at')
            ->get();
        // dd($campaign);

        if ($campaign->count() > 0) {
            $maxDialTries = 3;
            $referenceTable = $campaign[0]->camp_reference_table;

            $refColumns = [];
            foreach($campaign AS $keyCampaign => $valCampaign) {
                $refColumns[] = $referenceTable . '.' . strtolower(preg_replace('/\W+/i', '_', $valCampaign->th_name));
            }
            // dd($refColumns);

            $campaignContactsInfo = DB::table($referenceTable)
                ->selectRaw("
                    contacts.id AS cont_id, contacts.call_dial AS cont_call_dial, contacts.call_response AS cont_call_response,
                    contacts.total_calls AS cont_total_calls,"
                    . implode(', ', $refColumns) . ",
                    (SELECT SUM(contacts.total_calls) FROM contacts WHERE campaign_id = " . $campaign[0]->camp_id . ") AS sum_total_calls, 
                    (SELECT MIN(call_logs.call_dial) FROM call_logs WHERE campaign_id = " . $campaign[0]->camp_id . ") AS started,
                    (SELECT MAX(call_logs.call_dial) FROM call_logs WHERE campaign_id = " . $campaign[0]->camp_id . ") AS finished,
                    (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id=" . $campaign[0]->camp_id . " AND (contacts.total_calls=" . $maxDialTries . " OR contacts.call_response='answered')) AS completed_dials,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='answered' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS success,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='busy' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS busy,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='no_answer' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS no_answer,
                    (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='failed' AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS failed,
                    (SELECT COUNT(call_logs.id) FROM call_logs WHERE call_logs.call_dial IS NOT NULL AND call_logs.campaign_id=" . $campaign[0]->camp_id . ") AS dialed_contacts
                ")
                ->leftJoin('contacts', $referenceTable . '.contact_id', '=', 'contacts.id')
                ->where($referenceTable . '.campaign_id', $campaign[0]->camp_id)
                ->get();
            // dd($campaignContactsInfo);

            $fileName = 'REPORT_CAMPAIGN'
                . '-' . strtoupper(preg_replace('/\W+/i', '_', $campaign[0]->camp_name))
                . '-' . Carbon::now('Asia/Jakarta')->format('d_m_Y-H_i_s');
            
            if ($request->export_type === 'pdf') {
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadView('campaign.show_pdf', [
                    'campaign' => $campaign,
                    'campaignContactsInfo' => $campaignContactsInfo,
                ]);
                return $pdf->download($fileName . '.pdf');
            }
            else if ($request->export_type === 'excel') {
                Excel::create($fileName, function($excel) use($campaign, $campaignContactsInfo) {
                    $excel->sheet('Sheet1', function($sheet) use($campaign, $campaignContactsInfo) {
                        // ---
                        // --- create report headers starts
                        // ---
                        switch ($campaign[0]->camp_status) {
                            case 0: $campaign[0]->camp_status = 'Ready'; break;
                            case 1: $campaign[0]->camp_status = 'Running'; break;
                            case 2: $campaign[0]->camp_status = 'Paused'; break;
                            case 3: $campaign[0]->camp_status = 'Finished'; break;
                            default: break;
                        }
                        
                        // --- column A
                        $sheet->cell('A1', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('NAME'); });
                        $sheet->cell('A2', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaign[0]->camp_name); });

                        $sheet->cell('A4', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('TOTAL DATA'); });
                        $sheet->cell('A5', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaign[0]->camp_total_data); });

                        $sheet->cell('A7', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('CAMPAIGN PROGRESS (%)'); });
                        $sheet->cell('A8', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaignContactsInfo[0]->completed_dials); });

                        $sheet->cell('A10', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('STATUS'); });
                        $sheet->cell('A11', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaign[0]->camp_status); });

                        // --- column C
                        $sheet->cell('C1', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('CREATED DATE'); });
                        $sheet->cell('C2', function($cell) use($campaign, $campaignContactsInfo) {
                            $cell->setValue(date('d/m/Y - H:i', strtotime($campaign[0]->camp_created_at)));
                        });
                        
                        $sheet->cell('C4', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('DATE STARTED'); });
                        $sheet->cell('C5', function($cell) use($campaign, $campaignContactsInfo) {
                            $cell->setValue($campaignContactsInfo[0]->started ? date('d/m/Y - H:i', strtotime($campaignContactsInfo[0]->started)) : '-');
                        });

                        $sheet->cell('C7', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('DATE FINISHED'); });
                        $sheet->cell('C8', function($cell) use($campaign, $campaignContactsInfo) {
                            $cell->setValue(($campaignContactsInfo[0]->finished != '-') ? date('d/m/Y - H:i', strtotime($campaignContactsInfo[0]->finished)) : '-');
                        });

                        // --- column E
                        $sheet->cell('E1', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('TOTAL CALLS'); });
                        $sheet->cell('E2', function($cell) use($campaign, $campaignContactsInfo) {
                            $cell->setValue(
                                $campaignContactsInfo[0]->success +
                                $campaignContactsInfo[0]->no_answer +
                                $campaignContactsInfo[0]->busy +
                                $campaignContactsInfo[0]->failed
                            );
                        });

                        $sheet->cell('E4', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('SUCCESS CALLS'); });
                        $sheet->cell('E5', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaignContactsInfo[0]->success); });

                        $sheet->cell('E7', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('NO ANSWER CALLS'); });
                        $sheet->cell('E8', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaignContactsInfo[0]->no_answer); });

                        $sheet->cell('E10', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('BUSY CALLS'); });
                        $sheet->cell('E11', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaignContactsInfo[0]->busy); });

                        $sheet->cell('E13', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue('FAILED CALLS'); });
                        $sheet->cell('E14', function($cell) use($campaign, $campaignContactsInfo) { $cell->setValue($campaignContactsInfo[0]->failed); });


                        // --
                        // -- create template titles
                        // --
                        if ($campaign->count() > 0) {
                            $tempRowContent = [];
                            $tempColExtensions = [];
                
                            foreach ($campaign AS $keyHeader => $valHeader) {
                                // $tempColExtensions = [];
                                // if ($valHeader->th_is_mandatory) $tempColExtensions[] = 'mandatory';
                                // if ($valHeader->th_is_unique) $tempColExtensions[] = 'unique';
                                // if ($valHeader->th_is_voice) $tempColExtensions[] = 'voice-' . $valHeader->th_voice_position;
                                // $tempColName = strtoupper($valHeader->th_name) . ' (' . implode(', ', $tempColExtensions) . ')';

                                $tempColName = strtoupper($valHeader->th_name);
                                $tempRowContent[] = $tempColName;
                            }

                            $tempRowContent[] = 'CALL_DATE';
                            $tempRowContent[] = 'CALL_RESPONSE';
                            $tempRowContent[] = 'TOTAL_CALLS';

                            $sheet->row(17, $tempRowContent);
                        }


                        // ---
                        // --- populate data into excel
                        // ---
                        $excelRowNumber = 18;
                        $headerName = '';
                        $tempContactRow = [];

                        foreach ($campaignContactsInfo->chunk(100) as $chunks) {
                            foreach ($chunks as $valueContact) {
                                $tempContactRow = [];

                                foreach($campaign AS $keyHeader => $valHeader) {
                                    $headerName = strtolower(preg_replace('/\W+/i', '_', $valHeader->th_name));

                                    switch ($valHeader->th_type) {
                                        case 'handphone':
                                            $tempContactRow[] = substr($valueContact->$headerName, 0, 4)
                                                . 'xxxxxx'
                                                . substr($valueContact->$headerName, strlen($valueContact->$headerName) - 3);
                                            break;
                                        default:
                                            $tempContactRow[] = $valueContact->$headerName;
                                            break;
                                    }
                                }

                                $tempContactRow[] = $valueContact->cont_call_dial ? $valueContact->cont_call_dial : '';
                                $tempContactRow[] = $valueContact->cont_call_response ? strtoupper($valueContact->cont_call_response) : '';
                                $tempContactRow[] = $valueContact->cont_total_calls ? $valueContact->cont_total_calls : 0;

                                $sheet->row($excelRowNumber, $tempContactRow);
                                $excelRowNumber++;
                            }
                        }
                    });
                })->download('xlsx');
            }
        }
        else {
            return back();
        }
    }
    
    private function getTemplates()
    {
        $templates = Template::select(
                'templates.id', 'templates.name AS template_name', 'templates.reference_table', 'templates.voice_text',
                'template_headers.name AS header_name', 'template_headers.column_type', 'template_headers.is_mandatory',
                'template_headers.is_unique', 'template_headers.is_voice', 'template_headers.voice_position'
            )
            ->rightJoin('template_headers', 'templates.id', '=', 'template_headers.template_id')
            ->whereNull('templates.deleted_at')
            ->whereNotNull('template_headers.template_id')
            ->orderBy('templates.name', 'ASC')
            ->orderBy('template_headers.id', 'ASC')
            ->get();

        $tempTemplates = [];
        $defaultTemplate = [];
        foreach ($templates AS $keyTemplate => $valTemplate) {
            if (!isset($tempTemplates['t_' . $valTemplate->id])) {
                if ($valTemplate->id == 1) {
                    $defaultTemplate['t_' . $valTemplate->id]['id'] = $valTemplate->id;
                    $defaultTemplate['t_' . $valTemplate->id]['name'] = $valTemplate->template_name;
                    $defaultTemplate['t_' . $valTemplate->id]['voice_text'] = $valTemplate->voice_text;
                    $defaultTemplate['t_' . $valTemplate->id]['headers'] = [];

                    $tempTemplates = array_merge($defaultTemplate, $tempTemplates);
                }
                else {
                    $tempTemplates['t_' . $valTemplate->id]['id'] = $valTemplate->id;
                    $tempTemplates['t_' . $valTemplate->id]['name'] = $valTemplate->template_name;
                    $tempTemplates['t_' . $valTemplate->id]['voice_text'] = $valTemplate->voice_text;
                    $tempTemplates['t_' . $valTemplate->id]['headers'] = [];
                }
            }

            $tempTemplates['t_' . $valTemplate->id]['headers'][] = $valTemplate;
        }
        // dd($tempTemplates);
        return $tempTemplates;
    }
}
