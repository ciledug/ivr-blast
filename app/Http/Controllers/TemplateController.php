<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

use App\Campaign;
use App\CampaignCustomReportHeader;
use App\ColumnType;
use App\Template;
use App\TemplateHeader;

use Maatwebsite\Excel\Facades\Excel;

class TemplateController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = Template::whereNull('deleted_at')->paginate(15);
        return view('template.index', array(
            'templates' => $templates,
            'row_number' => $templates->firstItem(),
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('template.create', array(
            'column_types' => ColumnType::whereNull('deleted_at')->get(),
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
            'input_template_name' => 'required|string|min:5|max:30',
            'input_reference_table' => 'required|string|min:5|max:30',
            'column_names.*' => 'required|string|min:4|max:20',
            'column_types.*' => 'required|string|min:4|max:10',

            'input_template_voice_text' => 'nullable|string',
            'radio_mandatories.*' => 'nullable|string|min:2|max:3',
            'radio_uniques.*' => 'nullable|string|min:2|max:3',
            'radio_voices.*' => 'nullable|string|min:2|max:3',
            'voice_positions.*' => 'nullable|numeric|min:1|max:15',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $referenceTable = 't_' . substr($request->input_reference_table, 0, 6) . '_' . substr(Carbon::now('Asia/Jakarta')->getTimestamp(), -7);

        $template = Template::create([
            'name' => $request->input_template_name,
            'reference_table' => $referenceTable,
            'voice_text' => $request->input_template_voice_text,
        ]);

        Schema::create($referenceTable, function (Blueprint $table) use($request, $template) {
            $templateId = $template->id;
            $columnNames = $request->column_names;
            $columnTypes = $request->column_types;
            $columnMandatories = $request->radio_mandatories;
            $columnUniques = $request->radio_uniques;
            $columnVoices = $request->radio_voices;
            $columnVoicePos = $request->voice_positions;
            $columnNamesAndTypes = array();

            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned()->nullable();
            $table->integer('contact_id')->unsigned()->nullable();

            foreach($columnNames AS $keyName => $valName) {
                $columnTableName = strtolower(preg_replace('/\W+/i', '_', $valName));
                $columnType = $columnTypes[$keyName];

                switch ($columnType) {
                    case 'string':
                    case 'handphone':
                        $columnType = 'string';
                        break;
                    case 'numeric': $columnType = 'integer'; break;
                    case 'datetime': $columnType = 'datetime'; break;
                    case 'date': $columnType = 'date'; break;
                    case 'time': $columnType = 'time'; break;
                    default: break;
                }

                if ($columnMandatories[$keyName] && $columnUniques[$keyName]) {
                    $table->$columnType($columnTableName)->unique();
                }
                else if ($columnMandatories[$keyName]) {
                    $table->$columnType($columnTableName);
                }
                else if($columnUniques[$keyName]) {
                    $table->$columnType($columnTableName)->unique()->nullable();
                }
                else {
                    $table->$columnType($columnTableName)->nullable();
                }

                TemplateHeader::create([
                    'template_id' => $templateId,
                    'name' => $columnTableName,
                    'column_type' => $columnTypes[$keyName],
                    'is_mandatory' => $columnMandatories[$keyName] ? true : false,
                    'is_unique' => $columnUniques[$keyName] ? true : false,
                    'is_voice' => $columnVoices[$keyName] ? true : false,
                    'voice_position' => $columnVoicePos[$keyName],
                ]);
            }
            
            // $table->integer('extension')->unsigned()->nullable();
            // $table->string('callerid')->nullable();
            // $table->string('voice')->nullable();
            // $table->tinyInteger('total_calls')->unsigned()->nullable();
            // $table->datetime('call_dial')->index()->nullable();
            // $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
            $table->timestamps();
        });
        
        // dd($request->input());
        return redirect()->route('templates.index');
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
        $template = Template::whereNull('deleted_at')
            ->find($id);

        if ($template) {
            $template->delete();
        }

        return redirect()->route('templates.index');
    }

    public function download($id)
    {
        $tempFileName = 'dummy';
        $tempHeaders = Template::select(
                'templates.name AS templ_name',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('template_headers', 'templates.id', '=', 'template_headers.template_id')
            ->where('templates.id', $id)
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
}
