<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Campaign;
use App\Contact;

class ContactController extends Controller
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
        $tempContent = [];

        $contactHeaders = Contact::select(
                'contacts.id AS cont_id', 'contacts.phone AS cont_phone', 'contacts.total_calls AS cont_total_calls', 'contacts.nominal AS cont_nominal',
                'campaigns.id AS camp_id', 'campaigns.template_id AS camp_templ_id', 'campaigns.reference_table AS camp_reference_table',
                'template_headers.name AS th_name', 'template_headers.column_type AS th_column_type', 'template_headers.is_mandatory AS th_is_mandatory',
                'template_headers.is_unique AS th_is_unique', 'template_headers.is_voice AS th_is_voice', 'template_headers.voice_position AS th_voice_position'
            )
            ->leftJoin('campaigns', 'contacts.campaign_id', '=', 'campaigns.id')
            ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
            ->where('contacts.id', $id)
            ->whereNull('campaigns.deleted_at')
            ->groupBy('template_headers.id')
            ->get();
        // dd($contactHeaders);

        if ($contactHeaders->count() > 0) {
            $referenceTable = $contactHeaders[0]->camp_reference_table;

            $callLogsContent = DB::table($referenceTable)
                ->select(
                    $referenceTable . '.*',
                    'call_logs.call_dial AS cl_call_dial', 'call_logs.call_connect AS cl_call_connect', 'call_logs.call_disconnect AS cl_call_disconnect',
                    'call_logs.call_duration AS cl_call_durations', 'call_logs.call_recording AS cl_call_recording', 'call_logs.call_response AS cl_call_response',
                    'call_logs.created_at AS cl_created_at'
                )
                ->leftJoin('call_logs', $referenceTable . '.contact_id', '=', 'call_logs.contact_id')
                ->where($referenceTable . '.campaign_id', $contactHeaders[0]->camp_id)
                ->where($referenceTable . '.contact_id', $contactHeaders[0]->cont_id)
                ->orderBy('call_logs.id', 'DESC')
                ->get();
            // dd($callLogsContent);
        }

        return view('contact.show', [
            'contact_headers' => $contactHeaders,
            'call_logs_content' => $callLogsContent,
        ]);
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
