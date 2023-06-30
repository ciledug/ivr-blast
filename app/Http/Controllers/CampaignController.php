<?php

namespace App\Http\Controllers;

// use Anouar\Fpdf\Facades\Fpdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

use App\CallLog;
use App\Campaign;
use App\Contact;
use App\Template;
use App\TemplateHeader;
use Maatwebsite\Excel\Facades\Excel;

use App;
use PDF;

use App\Helpers\Helpers;

class CampaignController extends Controller
{
    static private $totalContactRows = 0;
    static private $newContacts = [];
    static private $existingContacts = [];
    static private $isStillProgress = true;

    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $campaigns = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.reference_table', 'campaigns.created_at',
                'users.name AS created_by'
            )
            ->leftjoin('users', 'campaigns.created_by', '=', 'users.id')
            ->orderBy('campaigns.id', 'DESC')
            ->paginate(15);
        // dd($campaigns);

        foreach ($campaigns AS $keyCampaign => $valCampaign) {
            $dialed = DB::table($valCampaign->reference_table)
                ->whereNotNull('call_dial')
                ->count('call_dial');
            $campaigns[$keyCampaign]['dialed_contacts'] = $dialed;
        }

        $rowNumber = $campaigns->firstItem();

        return view('campaign.index', [
            'campaigns' => $campaigns,
            'row_number' => $rowNumber,
        ]);
    }

    /*
    public function index()
    {
        $campaigns = Campaign::select(
                'campaigns.id', 'campaigns.name', 'campaigns.total_data', 'campaigns.status', 'campaigns.reference_table', 'campaigns.created_at'
            )
            ->selectRaw('
                users.name AS created_by,
                (SELECT COUNT(contacts.call_dial) FROM contacts WHERE contacts.campaign_id = campaigns.id AND contacts.call_dial IS NOT NULL) AS dialed_contacts
            ')
            ->leftjoin('users', 'campaigns.created_by', '=', 'users.id')
            ->orderBy('campaigns.id', 'DESC')
            ->paginate(15);

        $rowNumber = $campaigns->firstItem();

        return view('campaign.index', [
            'campaigns' => $campaigns,
            'row_number' => $rowNumber,
        ]);
    }
    */

    public function create(Request $request)
    {
        return view('campaign.create', array(
            'templates' => $this->getTemplates(),
        ));
    }

    /*
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
    */

    public function show(Request $request, $id)
    {
        $data = array(
            'row_number' => 0,
            'campaign' => [],
            'campaign_info' => [],
            'contacts' => [],
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
            $referenceTable = $campaign[0]->camp_ref_table;
            $campaignInfo = DB::table($referenceTable)
                ->selectRaw("
                    SUM(" . $referenceTable . ".total_calls) AS total_calls, 
                    (SELECT MIN(" . $referenceTable . ".call_dial) FROM " . $referenceTable . ") AS started,
                    (SELECT MAX(" . $referenceTable . ".call_dial) FROM " . $referenceTable . ") AS finished,
                    (SELECT COUNT(" . $referenceTable . ".call_response) FROM " . $referenceTable . " WHERE " . $referenceTable . ".call_response = 'answered') AS success,
                    (SELECT COUNT(" . $referenceTable . ".call_response) FROM " . $referenceTable . " WHERE " . $referenceTable . ".call_response = 'busy') AS busy,
                    (SELECT COUNT(" . $referenceTable . ".call_response) FROM " . $referenceTable . " WHERE " . $referenceTable . ".call_response = 'no_answer') AS no_answer,
                    (SELECT COUNT(" . $referenceTable . ".call_response) FROM " . $referenceTable . " WHERE " . $referenceTable . ".call_response = 'failed') AS failed,
                    (SELECT COUNT(" . $referenceTable . ".id) FROM " . $referenceTable . " WHERE " . $referenceTable . ".call_dial IS NOT NULL) AS dialed_contacts
                ")
                ->get();
            // dd($campaignInfo[0]);

            $data['contacts'] = DB::table($referenceTable)->paginate(15);
            $data['row_number'] = $data['contacts']->firstItem();
            $data['campaign'] = $campaign;
            $data['campaign_info'] = $campaignInfo[0];
        }
        // dd($data);

        return view('campaign.show', $data);
    }

    public function edit(Request $request, $id)
    {
        $data = array();

        if (session()->has('failed_contacts')) {
            $data = array(
                'name' => session('name'),
                'failed_contacts' => session('failed_contacts'),
            );
            session()->forget('name');
            session()->forget('key');
            session()->forget('saved_contacts');
            session()->forget('failed_contacts');
            // dd($data);
        }
        else {
            $data = array(
                'row_number' => 0,
                'campaign' => array(),
                'contacts' => array(),
                'templates' => $this->getTemplates(),
            );
    
            $campaignData = Campaign::select(
                    'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.reference_table AS camp_reference_table',
                    'campaigns.status AS camp_status', 'campaigns.template_id AS camp_templ_id', 'campaigns.text_voice AS camp_text_voice',
                    'campaigns.voice_gender AS camp_voice_gender',
                    'template_headers.name AS templ_header_name', 'template_headers.column_type AS templ_column_type',
                    'template_headers.is_mandatory AS templ_is_mandatory', 'template_headers.is_unique AS templ_is_unique',
                    'template_headers.is_voice AS templ_is_voice', 'template_headers.voice_position AS templ_voice_position'
                )
                ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
                ->where('campaigns.id', $id)
                ->whereNull('campaigns.deleted_at')
                ->get();
            // dd($campaignData);
    
            if ($campaignData->count() > 0) {
                $tempHeaders = [];
                $referenceTable = '';
                foreach ($campaignData AS $keyCampaign => $valCampaign) {
                    $tempHeaders[] = strtolower(preg_replace('/\W+/i', '_', $valCampaign->templ_header_name));
                    $referenceTable = $campaignData[$keyCampaign]->camp_reference_table;
                }
                
                $contacts = DB::table($referenceTable)
                    ->selectRaw(
                        implode(', ', $tempHeaders) . ', ' .
                        '(SELECT CAST(SUM(IF(call_dial IS NOT NULL, 1, 0)) AS INT) AS dialed_contacts FROM ' . $referenceTable . ') AS dialed_contacts '
                        
                    )
                    ->orderBy('id', 'ASC')
                    ->paginate(15);
                // dd($contacts->toSql());
                // dd($contacts);
                
                $data['row_number'] = $contacts->firstItem();
                $data['campaign'] = $campaignData;
                $data['contacts'] = $contacts;
            }
        }
        // dd($data);

        return view('campaign.edit', $data);
    }

    /*
    public function edit(Request $request, $id)
    {
        if (session()->has('failed_contacts')) {
            $data = array(
                'name' => session('name'),
                'failed_contacts' => session('failed_contacts'),
            );
            session()->forget('name');
            session()->forget('key');
            session()->forget('saved_contacts');
            session()->forget('failed_contacts');
            // dd($data);
        }
        else {
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
        }

        return view('campaign.edit', $data);
    }
    */

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
        // dd($request->input());

        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:50',
            'select_campaign_template' => 'required|numeric|min:1',
            'template_reference' => 'required|string|min:5|max:100',
            'campaign_text_voice' => 'nullable|string',
            'campaign_input_voice_gender' => 'nullable|string|min:10|max:15',
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
                $reportHeaders = $this->getTemplateHeaders($request->select_campaign_template);

                if ($reportHeaders->count() > 0) {
                    // ---
                    // --- create reference table and call-logs table
                    // ---
                    try {
                        Schema::create($request->template_reference, function (Blueprint $table) use($reportHeaders) {
                            $table->engine = 'MyISAM';
                            $table->charset = 'utf8mb4';
                            $table->collation = 'utf8mb4_unicode_ci';
                
                            $table->increments('id');
                            $table->integer('contact_id')->unsigned()->nullable();

                            foreach($reportHeaders AS $keyHeader => $valHeader) {
                                $columnName = strtolower(preg_replace('/\W+/i', '_', $valHeader->name));
                                $columnType = $valHeader->column_type;

                                switch ($columnType) {
                                    case 'string':
                                    case 'handphone': $columnType = 'string'; break;
                                    case 'numeric': $columnType = 'integer'; break;
                                    case 'datetime':
                                    case 'date':
                                    case 'time':
                                        break;
                                    default: break;
                                }

                                if ($valHeader->is_mandatory && $valHeader->is_unique) {
                                    $table->$columnType($columnName)->unique();
                                }
                                else if ($valHeader->is_mandatory) {
                                    $table->$columnType($columnName);
                                }
                                else if($valHeader->is_unique) {
                                    $table->$columnType($columnName)->unique()->nullable();
                                }
                                else {
                                    $table->$columnType($columnName)->nullable();
                                }

                                $reportHeaders[$keyHeader]->name = $columnName;
                            }
                            
                            $table->integer('extension')->unsigned()->nullable();
                            $table->string('callerid')->nullable();
                            $table->string('voice')->nullable();
                            $table->tinyInteger('total_calls')->unsigned()->nullable();
                            $table->datetime('call_dial')->index()->nullable();
                            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                            $table->timestamps();
                        });

                        Schema::create($request->template_reference . '_call_logs', function (Blueprint $table) {
                            $table->engine = 'MyISAM';
                            $table->charset = 'utf8mb4';
                            $table->collation = 'utf8mb4_unicode_ci';
                            
                            $table->increments('id');
                            $table->integer('contact_id')->unsigned();
                            $table->datetime('call_dial')->nullable();
                            $table->datetime('call_connect')->nullable();
                            $table->datetime('call_disconnect')->nullable();
                            $table->integer('call_duration')->default(0)->nullable();
                            $table->string('call_recording', 255)->nullable();
                            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                            $table->timestamps();
                        });

                        $campaignCreate = Campaign::create(array(
                            'unique_key' => $todayDateTime->getTimestamp(),
                            'name' => $request->name,
                            'created_by' => Auth::user()->id,
                            'template_id' => $request->select_campaign_template,
                            'reference_table' => $request->template_reference,
                            'text_voice' => trim($request->campaign_text_voice),
                            'voice_gender' => $request->campaign_input_voice_gender,
                        ));

                        list($newContacts, $existingContacts) = $this->saveValidContacts(
                            $campaignCreate->id,
                            $dataRows,
                            $request->template_reference,
                            $reportHeaders,
                            $request->campaign_text_voice
                        );

                        $campaignCreate->total_data += count($dataRows) - count($existingContacts);
                        $campaignCreate->save();

                        if (count($existingContacts) <= 0) {
                            return redirect()->route('campaigns');
                        }
                        else {
                            return redirect()->route('campaigns.edit', ['id' => $campaignCreate->id])->with([
                                'name' => $campaignCreate->name,
                                'saved_contacts' => json_encode($newContacts),
                                'failed_contacts' => json_encode($existingContacts)
                            ]);
                        }
                        
                    } catch (\Illuminate\Database\QueryException $ex) {
                        // dd($ex);
                        $validator->errors()->add(
                            'campaign_referense_exists',
                            'Campaign Name with selected Campaign Template already exists.
                        ');
                        return back()->withErrors($validator)->withInput();
                    }
                }
                else {
                    $validator->errors()->add('no_template_headers', 'Selected template doesn\'t have headers.');
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

    public function update(Request $request)
    {
        // dd($request->input());
        $validator = Validator::make($request->input(), [
            'campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'campaign_name' => 'required|string|min:5|max:50',
            'select_campaign_template' => 'required|numeric|min:1|max:' . Template::max('id'),
            'campaign_voice_gender' => 'required|string|min:10|max:15',

            'campaign_edit_action' => 'nullable|string|min:5|max:8',
            'campaign_text_voice' => 'nullable|string',
            'template_reference' => 'nullable|string|min:25|max:25',
            'contact_rows' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // $request->campaign = 1; // for testing n debugging
        $campaignAndHeaders = Campaign::select(
                'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.unique_key AS camp_unique_key', 'campaigns.total_data AS camp_total_data',
                'campaigns.template_id AS camp_templ_id', 'campaigns.reference_table AS camp_reference_table',
                'template_headers.name AS templ_name', 'template_headers.column_type AS templ_column_type'
            )
            ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
            ->where('campaigns.id', $request->campaign)
            ->where('campaigns.status', '=', 0)
            ->whereNull('template_headers.deleted_at')
            ->get();
        // dd($campaign);
        

        if ($campaignAndHeaders->count() > 0) {
            $newContacts = [];
            $existingContacts = [];

            $campaign = $campaignAndHeaders[0];
            $dataRows = json_decode($request->contact_rows); // dd($dataRows);
            $totalContactRows = count($dataRows);
            $reportHeaders = $this->getTemplateHeaders($request->select_campaign_template, true); // dd($reportHeaders);
            $postedTemplateReference = strtolower(trim($request->template_reference));
            
            // ---
            // --- check for template change if any
            // --- and create the new necessary contacts and call-logs table
            // ---
            if ($campaign->camp_templ_id != $request->select_campaign_template) {
                if ($reportHeaders->count() > 0) {
                    try {
                        Schema::dropIfExists($campaign->camp_reference_table);
                        Schema::dropIfExists($campaign->camp_reference_table . '_call_logs');

                        Schema::create($postedTemplateReference, function (Blueprint $table) use($reportHeaders) {
                            // $table->engine = 'MyISAM';
                            $table->charset = 'utf8mb4';
                            $table->collation = 'utf8mb4_unicode_ci';

                            $table->increments('id');
                            $table->integer('contact_id')->unsigned()->nullable();

                            foreach($reportHeaders AS $keyHeader => $valHeader) {
                                $columnName = strtolower(preg_replace('/\W+/i', '_', $valHeader->name));
                                $columnType = $valHeader->column_type;

                                switch ($columnType) {
                                    case 'string':
                                    case 'handphone': $columnType = 'string'; break;
                                    case 'numeric': $columnType = 'integer'; break;
                                    case 'datetime':
                                    case 'date':
                                    case 'time':
                                        break;
                                    default: break;
                                }

                                if ($valHeader->is_mandatory && $valHeader->is_unique) {
                                    $table->$columnType($columnName)->unique();
                                }
                                else if ($valHeader->is_mandatory) {
                                    $table->$columnType($columnName);
                                }
                                else if($valHeader->is_unique) {
                                    $table->$columnType($columnName)->unique()->nullable();
                                }
                                else {
                                    $table->$columnType($columnName)->nullable();
                                }

                                $reportHeaders[$keyHeader]->name = $columnName;
                            }

                            $table->integer('extension')->unsigned()->nullable();
                            $table->string('callerid')->nullable();
                            $table->string('voice')->nullable();
                            $table->tinyInteger('total_calls')->unsigned()->nullable();
                            $table->datetime('call_dial')->index()->nullable();
                            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                            $table->timestamps();
                        });

                        Schema::create($postedTemplateReference . '_call_logs', function (Blueprint $table) {
                            $table->engine = 'MyISAM';
                            $table->charset = 'utf8mb4';
                            $table->collation = 'utf8mb4_unicode_ci';
                            
                            $table->increments('id');
                            $table->integer('contact_id')->unsigned();
                            $table->datetime('call_dial')->nullable();
                            $table->datetime('call_connect')->nullable();
                            $table->datetime('call_disconnect')->nullable();
                            $table->integer('call_duration')->default(0)->nullable();
                            $table->string('call_recording', 255)->nullable();
                            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                            $table->timestamps();
                        });

                        $campaign->camp_reference_table = $postedTemplateReference;

                    } catch (\Illuminate\Database\QueryException $ex) {
                        dd($ex);
                        $validator->errors()->add(
                            'campaign_referense_exists',
                            'Campaign Name with selected Campaign Template already exists.
                        ');
                        return back()->withErrors($validator)->withInput();
                    }
                }
                else {
                    return back()->with([
                        'name' => $campaign->camp_name,
                        'key' => $campaign->camp_unique_key,
                        'template_not_exists' => 'This template does not exist.',
                    ]);
                }
            }

            // ---
            // --- check for campaign's name change if any
            // --- and change the related contacts and call-logs table's name
            // ---
            if ($campaign->camp_reference_table !== $postedTemplateReference) {
                Schema::rename($campaign->camp_reference_table, $postedTemplateReference);
                Schema::rename($campaign->camp_reference_table . '_call_logs', $postedTemplateReference . '_call_logs');
            }

            // ---
            // --- check for data action change
            // ---
            if ($request->campaign_edit_action) {
                // ---
                // --- check if data action is 'replace'
                // ---
                if ($request->campaign_edit_action === 'replace') {
                    DB::table($postedTemplateReference)->truncate();
                    DB::table($postedTemplateReference . '_call_logs')->truncate();
                    $campaign->camp_total_data = 0;
                }
                
                // ---
                // --- check if there's new contact list
                // ---
                if (count($dataRows) > 0) {
                    list($newContacts, $existingContacts) = $this->saveValidContacts(
                        $campaign->camp_id,
                        $dataRows,
                        $postedTemplateReference,
                        $reportHeaders,
                        $request->campaign_text_voice
                    );
                    // dd(count($newContacts));
                    // dd($existingContacts);
                }
            }
            
            // ---
            // --- process the update
            // ---
            Campaign::where('id', $campaign->camp_id)
                ->whereNull('deleted_at')
                ->update([
                    'name' => $request->campaign_name,
                    'total_data' => $campaign->camp_total_data + count($newContacts),
                    'template_id' => $request->select_campaign_template,
                    'reference_table' => $postedTemplateReference,
                    'text_voice' => trim($request->campaign_text_voice),
                    'voice_gender' => $request->campaign_voice_gender,
                ]);
            // dd('samape sini');
            
            //
            // --- redirects
            // ---
            if (empty($existingContacts)) {
                return redirect()->route('campaigns');
            }
            else {
                $campaignFailedContact = Campaign::select(
                        'campaigns.id AS camp_id', 'campaigns.name AS camp_name', 'campaigns.unique_key AS camp_unique_key',
                        'campaigns.total_data AS camp_total_data',
                        'campaigns.template_id AS camp_templ_id', 'campaigns.reference_table AS camp_reference_table',
                        'template_headers.name AS templ_name', 'template_headers.column_type AS templ_column_type',
                        'template_headers.is_mandatory AS templ_is_mandatory', 'template_headers.is_unique AS templ_is_unique',
                        'template_headers.is_voice AS templ_is_voice', 'template_headers.voice_position AS templ_voice_position'
                    )
                    ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
                    ->where('campaigns.id', $request->campaign)
                    ->where('campaigns.status', '=', 0)
                    ->whereNull('template_headers.deleted_at')
                    ->get();
                // dd($campaignFailedContact);
                
                $returnedData = [
                    'campaign_failed_contacts' => $campaignFailedContact,
                    'saved_contacts' => json_encode($newContacts),
                    'failed_contacts' => json_encode($existingContacts),
                ];
                // dd($returnedData);
                
                return back()->with($returnedData);
            }
        }
        else {
            // dd('sampe sini');
            return back()->with([
                'name' => $campaign->name,
                'key' => $campaign->unique_key,
                'already_running' => 'This campaign does not exists or is being or has been processed.',
            ]);
        }
    }
    
    /*
    public function update(Request $request)
    {
        // dd($request->input());
        $validator = Validator::make($request->input(), [
            'campaign_name' => 'required|string|min:5|max:30',
            'rows' => 'nullable|string',
            'campaign_edit_action' => 'nullable|string|max:8',
            'campaign' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // $request->campaign = 1; // for testing n debugging
        $campaign = Campaign::select('campaigns.id')
            ->selectRaw('
                CAST(
                    SUM(
                        IF(contacts.total_calls IS NOT NULL, contacts.total_calls, 0)
                    )
                    AS UNSIGNED
                ) AS total_calls'
            )
            ->where(function($q) {
                $q->where('campaigns.status', '=', 0)
                  ->orWhere('campaigns.status', '=', 2);
            })
            ->leftJoin('contacts', 'campaigns.id', '=', 'contacts.campaign_id')
            ->find($request->campaign);
        // dd($campaign);

        if ($campaign->id && ($campaign->total_calls == 0)) {
            $dataRows = json_decode($request->rows); // dd($dataRows);
            $newContacts = [];
            $existingContacts = [];
            $totalContactRows = count($dataRows);

            if ($request->campaign_edit_action && ($totalContactRows > 0)) {
                if ($request->campaign_edit_action === 'replace') {
                    Contact::where('campaign_id', '=', $campaign->id)->delete();
                    $campaign->total_data = 0;
                }

                list($newContacts, $existingContacts) = $this->saveValidContacts($campaign->id, $dataRows);
                // $campaign->total_data += count($newContacts);
                $campaign->total_data += ($totalContactRows - count($existingContacts));
            }

            $campaign->name = $request->campaign_name;
            $campaign->save();

            if (empty($existingContacts)) {
                return redirect()->route('campaigns');
            }
            else {
                return redirect()->route('campaigns.edit', ['id' => $campaign->id])->with([
                    'name' => $campaign->name,
                    'saved_contacts' => json_encode($newContacts),
                    'failed_contacts' => json_encode($existingContacts),
                ]);
            }
        }
        else {
            return back()->with([
                'name' => $campaign->name,
                'key' => $campaign->unique_key,
                'already_running' => 'This campaign does not exists or already run once.',
            ]);
        }
    }
    */

    public function destroy(Request $request)
    {
        $campaign = Campaign::find($request->campaign);

        if ($campaign) {
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
            'campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
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
            $campaignInfo = DB::table($campaign[0]->camp_reference_table)
                ->selectRaw("
                    SUM(" . $campaign[0]->camp_reference_table . ".total_calls) AS total_calls, 
                    (SELECT MIN(" . $campaign[0]->camp_reference_table . ".call_dial) FROM " . $campaign[0]->camp_reference_table . ") AS started,
                    (SELECT MAX(" . $campaign[0]->camp_reference_table . ".call_dial) FROM " . $campaign[0]->camp_reference_table . ") AS finished,
                    (SELECT COUNT(" . $campaign[0]->camp_reference_table . ".call_response) FROM " . $campaign[0]->camp_reference_table . " WHERE " . $campaign[0]->camp_reference_table . ".call_response = 'answered') AS success,
                    (SELECT COUNT(" . $campaign[0]->camp_reference_table . ".call_response) FROM " . $campaign[0]->camp_reference_table . " WHERE " . $campaign[0]->camp_reference_table . ".call_response = 'busy') AS busy,
                    (SELECT COUNT(" . $campaign[0]->camp_reference_table . ".call_response) FROM " . $campaign[0]->camp_reference_table . " WHERE " . $campaign[0]->camp_reference_table . ".call_response = 'no_answer') AS no_answer,
                    (SELECT COUNT(" . $campaign[0]->camp_reference_table . ".call_response) FROM " . $campaign[0]->camp_reference_table . " WHERE " . $campaign[0]->camp_reference_table . ".call_response = 'failed') AS failed,
                    (SELECT COUNT(" . $campaign[0]->camp_reference_table . ".id) FROM " . $campaign[0]->camp_reference_table . " WHERE " . $campaign[0]->camp_reference_table . ".call_dial IS NOT NULL) AS dialed_contacts
                ")
                ->first();
            // dd($campaignInfo);

            $progress = number_format(($campaignInfo->dialed_contacts / $campaign[0]->camp_total_data) * 100, 2, '.', ',');
            $contacts = DB::table($campaign[0]->camp_reference_table)->get();
            // dd($contacts);

            $fileName = 'REPORT_CAMPAIGN'
                . '-' . strtoupper(preg_replace('/\W+/i', '_', $campaign[0]->camp_name))
                . '-' . Carbon::now('Asia/Jakarta')->format('d_m_Y-H_i_s');
            
            if ($request->export_type === 'pdf') {
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadView('campaign.show_pdf', [
                    'campaign' => $campaign,
                    'contacts' => $contacts,
                    'campaignInfo' => $campaignInfo,
                    'progress' => $progress
                ]);
                return $pdf->download($fileName . '.pdf');
            }
            else if ($request->export_type === 'excel') {
                Excel::create($fileName, function($excel) use($campaign, $contacts, $campaignInfo, $progress) {
                    $excel->sheet('Sheet1', function($sheet) use($campaign, $contacts, $campaignInfo, $progress) {
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
                        $sheet->cell('A1', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('NAME'); });
                        $sheet->cell('A2', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaign[0]->camp_name); });

                        $sheet->cell('A4', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('TOTAL DATA'); });
                        $sheet->cell('A5', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaign[0]->camp_total_data); });

                        $sheet->cell('A7', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('CAMPAIGN PROGRESS (%)'); });
                        $sheet->cell('A8', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($progress); });

                        $sheet->cell('A10', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('STATUS'); });
                        $sheet->cell('A11', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaign[0]->camp_status); });

                        // --- column C
                        $sheet->cell('C1', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('CREATED DATE'); });
                        $sheet->cell('C2', function($cell) use($campaign, $contacts, $campaignInfo, $progress) {
                            $cell->setValue(date('d/m/Y - H:i', strtotime($campaign[0]->camp_created_at)));
                        });
                        
                        $sheet->cell('C4', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('DATE STARTED'); });
                        $sheet->cell('C5', function($cell) use($campaign, $contacts, $campaignInfo, $progress) {
                            $cell->setValue($campaignInfo->started ? date('d/m/Y - H:i', strtotime($campaignInfo->started)) : '-');
                        });

                        $sheet->cell('C7', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('DATE FINISHED'); });
                        $sheet->cell('C8', function($cell) use($campaign, $contacts, $campaignInfo, $progress) {
                            $cell->setValue(($campaignInfo->finished != '-') ? date('d/m/Y - H:i', strtotime($campaignInfo->finished)) : '-');
                        });

                        // --- column E
                        $sheet->cell('E1', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('TOTAL CALLS'); });
                        $sheet->cell('E2', function($cell) use($campaign, $contacts, $campaignInfo, $progress) {
                            $cell->setValue($campaignInfo->success + $campaignInfo->no_answer + $campaignInfo->busy + $campaignInfo->failed);
                        });

                        $sheet->cell('E4', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('SUCCESS CALLS'); });
                        $sheet->cell('E5', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaignInfo->success); });

                        $sheet->cell('E7', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('NO ANSWER CALLS'); });
                        $sheet->cell('E8', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaignInfo->no_answer); });

                        $sheet->cell('E10', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('BUSY CALLS'); });
                        $sheet->cell('E11', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaignInfo->busy); });

                        $sheet->cell('E13', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue('FAILED CALLS'); });
                        $sheet->cell('E14', function($cell) use($campaign, $contacts, $campaignInfo, $progress) { $cell->setValue($campaignInfo->failed); });


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

                        foreach ($contacts->chunk(100) as $chunks) {
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

                                $tempContactRow[] = $valueContact->call_dial ? $valueContact->call_dial : '';
                                $tempContactRow[] = $valueContact->call_response ? strtoupper($valueContact->call_response) : '';
                                $tempContactRow[] = $valueContact->total_calls ? $valueContact->total_calls : 0;

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

    /*
    public function downloadTemplate()
    {
        $fileTemplate = public_path('files/Template_IVR_Blast.xlsx');
        return response()->download($fileTemplate);
    }
    */

    public function downloadTemplate($templateId)
    {
        $tempFileName = 'dummy';
        $tempHeaders = Template::select(
                'templates.name AS templ_name',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('template_headers', 'templates.id', '=', 'template_headers.template_id')
            ->where('templates.id', $templateId)
            ->whereNull('templates.deleted_at')
            ->get();
        // dd($tempHeaders);

        if ($tempHeaders->count() > 0) $tempFileName = preg_replace('/\W+/i', '_', $tempHeaders[0]->templ_name);
        $tempFileName .= '_' . Carbon::now('Asia/Jakarta')->format('d_m_Y_H_i_s');

        Excel::create('TEMPLATE_' . $tempFileName, function($excel) use($tempFileName, $tempHeaders) {
            $tempRowContent = [];
            $tempColExtensions = [];

            foreach ($tempHeaders AS $keyHeader => $valHeader) {
                $tempColExtensions = [];
                if ($valHeader->th_is_mandatory) $tempColExtensions[] = 'mandatory';
                if ($valHeader->th_is_unique) $tempColExtensions[] = 'unique';
                if ($valHeader->th_is_voice) $tempColExtensions[] = 'voice-' . $valHeader->th_voice_position;
    
                $tempColName = strtoupper($valHeader->th_name) . ' (' . implode(', ', $tempColExtensions) . ')';
                $tempRowContent[] = $tempColName;
            }

            $excel->sheet('Sheet1', function($sheet) use($tempRowContent) {
                $sheet->row(1, $tempRowContent);
            });

        })->download('xlsx');
    }


    /*
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
    */
    
    public function exportFailedContacts(Request $request)
    {
        $validate = Validator::make($request->input(), [
            'failed_contacts_campaign' => 'required|numeric|min:1|max:' . Campaign::max('id'),
            'campaign_failed_contacts' => 'required|string',
            'campaign_failed_contacts_headers' => 'required|string'
        ]);

        if ($validate->fails()) {
            return back();
        }

        $failedContacts = json_decode($request->campaign_failed_contacts);
        $templateHeaders = json_decode($request->campaign_failed_contacts_headers);
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

    public function importProgress(Request $request)
    {
        $totalContacts = count(self::$totalContactRows);
        $newContactsCount = count(self::$newContacts);
        $existingContactsCount = count(self::$existingContacts);

        // $totalContacts = $this->totalContactRows;
        // $newContactsCount = count($this->newContacts);
        // $existingContactsCount = count($this->existingContacts);

        $isFinished = true;
        $progress = 0;
        // dd($totalContacts . ' - ' . $newContactsCount . ' - ' . $existingContactsCount);

        if (($totalContacts > 0) && (($newContactsCount > 0) || ($existingContactsCount > 0))) {
            $isFinished = ($newContactsCount + $existingContactsCount) != $totalContacts ? false : true;
            $progress = (($newContactsCount + $existingContactsCount) / $totalContacts) * 100;
        }

        $returnedData = json_encode([
            'is_finished' => $isFinished,
            'new_contacts' => $newContactsCount,
            'existing_contacts' => $existingContactsCount,
            'progress' => $progress,
        ]);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        echo "data: {$returnedData}\n\nretry:1000\n\n";
        flush();
    }

    /*
    private function saveValidContacts($campaignId, $dataRows)
    {
        dd($dataRows);

        ini_set('max_execution_time', 0);

        $newContacts = [];
        $existingContacts = [];

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

        foreach ($dataRows AS $keyDataRows => $valueDataRows) {
            $tempContact = [
                'campaign_id' => $campaignId,
                'account_id' => trim(preg_replace('/\s/', '', $valueDataRows->account_id)),
                'name' => $valueDataRows->name,
                'phone' => trim(preg_replace('/\D/', '', $valueDataRows->phone)),
                'bill_date' => $valueDataRows->bill_date,
                'due_date' => $valueDataRows->due_date,
                'nominal' => $valueDataRows->nominal,
            ];

            // -- connection with sip
            // $rand = rand(0, sipIdxCount);
            // $tempContact['extension']   = $sip[$rand]->extension;
            // $tempContact['callerid']    = $sip[$rand]->callerid;
            // $tempContact['voice']       = Helpers::generateVoice([
            //     'bill_date' => $valueDataRows->bill_date,
            //     'due_date' => $valueDataRows->due_date,
            //     'nominal' => $valueDataRows->nominal
            // ]);

            $first8Position = stripos($tempContact['phone'], '8', 0);

            if ($first8Position === false) {
                $tempContact['failed'] = 'Phone number error';
                $existingContacts[] = $tempContact;
                // $existingContacts[] = implode(';', $tempContact);
            }
            else {
                if ($first8Position > 5) {
                    $tempContact['failed'] = 'Phone number error';
                    $existingContacts[] = $tempContact;
                    // $existingContacts[] = implode(';', $tempContact);
                }
                else {
                    $tempContact['phone'] = '0' . substr($tempContact['phone'], $first8Position);
                    if ((strlen($tempContact['phone']) >= 10) && (strlen($tempContact['phone']) <= 15)) {
                        $contact = DB::insert('
                            INSERT IGNORE INTO contacts(campaign_id, account_id, name, phone, bill_date, due_date, nominal, extension, callerid, voice)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ', [
                            $tempContact['campaign_id'],
                            $tempContact['account_id'],
                            $tempContact['name'],
                            $tempContact['phone'],
                            $tempContact['bill_date'],
                            $tempContact['due_date'],
                            $tempContact['nominal'],
                            0, // $tempContact['extension'],
                            0, // $tempContact['callerid'],
                            '', // $tempContact['voice'],
                        ]);

                        $warningMessages = DB::select('SHOW WARNINGS'); // dd($warningMessages);

                        if (empty($warningMessages)) {
                            // $newContacts[] = $tempContact;
                            // $newContacts[] = implode(';', $tempContact);
                        } else {
                            $tempContact['failed'] = 'Phone number exists';
                            $existingContacts[] = $tempContact;
                            // $existingContacts[] = implode(';', $tempContact);
                        }

                        $contact = null;
                        $warningMessages = null;
                        unset($contact, $warningMessages);
                    }
                    else {
                        $tempContact['failed'] = 'Phone format error';
                        $existingContacts[] = $tempContact;
                        // $existingContacts[] = implode(';', $tempContact);
                    }
                }
            }

            $first8Position = null;
            $tempContact = null;
            $keyDataRows = null;
            $valueDataRows = null;
            unset($first8Position, $tempContact, $keyDataRows, $valueDataRows);
        }

        ini_set('max_execution_time', 120);
        return array($newContacts, $existingContacts);
    }
    */

    private function saveValidContacts($campaignId, $dataRows, $referenceTable, $reportHeaders, $textVoice)
    {
        ini_set('max_execution_time', 0);

        $newContacts = [];
        $existingContacts = [];

        // ---
        // --- create sql query for inserting data into reference table
        // --- get 'handphone' column type
        // --- get 'voice' column type
        // ---
        $phoneColumns = [];
        $voiceColumns = [];
        $refColumns = [ 'extension', 'callerid', 'voice', 'created_at', 'updated_at' ];
        $refBindings = [ '?', '?', '?', '?', '?' ];
        $insertRefTableCommand = '';
        if (count($reportHeaders)) {
            foreach ($reportHeaders  AS $keyHeader => $valHeader) {
                $refColumns[] = strtolower(preg_replace('/\W+/i', '_', $valHeader->name));
                $refBindings[] = '?';

                if ($valHeader->column_type == 'handphone') {
                    $phoneColumns[] = $valHeader;
                }

                if ($valHeader->is_voice) {
                    $voiceColumns[] = $valHeader;
                }
            }

            $insertRefTableCommand = 'INSERT IGNORE INTO ' . $referenceTable . ' (<ref_columns>) ';
            $insertRefTableCommand .= 'VALUES (<ref_bindings>)';
            $insertRefTableCommand = str_replace(
                array('<ref_columns>', '<ref_bindings>'),
                array(implode(', ', $refColumns), implode(', ', $refBindings)),
                $insertRefTableCommand
            );
        }
        // dd($voiceColumns);
        // dd($insertRefTableCommand);

        if (count($phoneColumns) > 0) {
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

            foreach ($dataRows AS $keyDataRows => $valDataRows) {
                $tempContact = array(
                    'extension' => null,
                    'callerid' => null,
                    'voice' => null,
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                );

                // ---
                // --- put excel data into template's columns
                // --- get the handphone column type
                // --- 
                // dd($reportHeaders);
                $phoneColumnName = '';
                $first8Position = '';
                foreach($reportHeaders  AS $keyHeader => $valHeader) {
                    $headerName = strtolower(preg_replace('/\W+/i', '_', $valHeader->name));
                    $tempContact[$headerName] = $valDataRows->$headerName;

                    if ($valHeader->column_type == 'handphone') {
                        $phoneColumnName = $headerName;
                        $first8Position = stripos($tempContact[$headerName], '8', 0);
                    }
                }
                
                if ($first8Position === false) {
                    $tempContact['failed'] = 'Phone number format error';
                    $existingContacts[] = $tempContact;
                    // $existingContacts[] = implode(';', $tempContact);
                }
                else {
                    if ($first8Position > 5) {
                        $tempContact['failed'] = 'Phone number error';
                        $existingContacts[] = $tempContact;
                        // $existingContacts[] = implode(';', $tempContact);
                    }
                    else {
                        $tempContact[$phoneColumnName] = '0' . substr($tempContact[$phoneColumnName], $first8Position);
                        
                        if ((strlen($tempContact[$phoneColumnName]) >= 10) && (strlen($tempContact[$phoneColumnName]) <= 15)) {
                            // -- connection with sip
                            /*
                            $rand = rand(0, sipIdxCount);
                            $tempContact['extension']   = $sip[$rand]->extension;
                            $tempContact['callerid']    = $sip[$rand]->callerid;
                            */
                            $tempContact['voice']       = ($textVoice != null) ? Helpers::generateVoice($textVoice, $voiceColumns, $valDataRows) : null;
                            
                            // ---
                            // --- insert ignore into DB and get the warnings if any
                            // ---
                            $contact = DB::insert($insertRefTableCommand, array_values($tempContact));
                            $warningMessages = DB::select('SHOW WARNINGS');
                            // dd($warningMessages);
    
                            if (empty($warningMessages)) {
                                $newContacts[] = $tempContact;
                                // $newContacts[] = implode(';', $tempContact);
                            }
                            else {
                                $tempContact['failed'] = 'Has same unique value';
                                $existingContacts[] = $tempContact;
                                // $existingContacts[] = implode(';', $tempContact);
                            }
    
                            $contact = null;
                            $warningMessages = null;
                            unset($contact, $warningMessages);
                        }
                        else {
                            $tempContact['failed'] = 'Phone number must between 10-15 digits';
                            $existingContacts[] = $tempContact;
                            // $existingContacts[] = implode(';', $tempContact);
                        }
                    }
                }
    
                $first8Position = null;
                $tempContact = null;
                $keyDataRows = null;
                $valueDataRows = null;
                unset($first8Position, $tempContact, $keyDataRows, $valueDataRows);
            }
        }
        else {
            // 
        }

        // dd($newContacts);

        ini_set('max_execution_time', 120);
        return array($newContacts, $existingContacts);
    }

    private function getTemplates()
    {
        $templates = Template::select(
                'templates.id', 'templates.name AS template_name',
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
                    $defaultTemplate['t_' . $valTemplate->id]['headers'] = [];

                    $tempTemplates = array_merge($defaultTemplate, $tempTemplates);
                }
                else {
                    $tempTemplates['t_' . $valTemplate->id]['id'] = $valTemplate->id;
                    $tempTemplates['t_' . $valTemplate->id]['name'] = $valTemplate->template_name;
                    $tempTemplates['t_' . $valTemplate->id]['headers'] = [];
                }
            }

            $tempTemplates['t_' . $valTemplate->id]['headers'][] = $valTemplate;
        }
        // dd($tempTemplates);
        return $tempTemplates;
    }

    private function getTemplateHeaders($templateId, $isOrderedByVoice=false)
    {
        $templateHeaders = TemplateHeader::where('template_id', $templateId)
            ->whereNull('deleted_at');

        if ($isOrderedByVoice) $templateHeaders->orderBy('voice_position', 'ASC');
        else $templateHeaders->orderBy('id', 'ASC');

        return $templateHeaders->get();
    }

    private function getTemplateHeadersTitle($templateId, $isOrderedByVoice=false)
    {
        $headers = $this->getTemplateHeaders($templateId, $isOrderedByVoice);
        foreach ($headers AS $key => $value) {
            $headers[$key] = strtolower($value->name);
        }
        return $headers;
    }
}
