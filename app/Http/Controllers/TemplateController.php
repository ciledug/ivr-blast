<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Campaign;
use App\CampaignCustomReportHeader;
use App\ColumnType;
use App\Template;
use App\TemplateHeader;

class TemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('template.index', array(
            'templates' => Template::whereNull('deleted_at')->get(),
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
            'name' => 'required|string|min:4|max:30',
            'column_names.*' => 'required|string|min:4|max:20',
            'column_types.*' => 'required|string|min:4|max:10',
            'radio_mandatories.*' => 'nullable|string|min:2|max:3',
            'radio_uniques.*' => 'nullable|string|min:2|max:3',
            'radio_voices.*' => 'nullable|string|min:2|max:3',
            'voice_positions.*' => 'nullable|numeric|min:1|max:15',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $template = Template::create([
            'name' => $request->name,
        ]);
        $templateId = $template->id;
        
        $columnNames = $request->column_names;
        $columnTypes = $request->column_types;
        $columnMandatories = $request->radio_mandatories;
        $columnUniques = $request->radio_uniques;
        $columnVoices = $request->radio_voices;
        $columnVoicePos = $request->voice_positions;
        $columnNamesAndTypes = array();

        foreach ($columnNames AS $keyName => $valName) {
            TemplateHeader::create([
                'template_id' => $templateId,
                'name' => $valName,
                'column_type' => $columnTypes[$keyName],
                'is_mandatory' => $columnMandatories[$keyName] ? true : false,
                'is_unique' => $columnUniques[$keyName] ? true : false,
                'is_voice' => $columnVoices[$keyName] ? true : false,
                'voice_position' => $columnVoicePos[$keyName],
            ]);
        }
        
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
        //
    }
}
