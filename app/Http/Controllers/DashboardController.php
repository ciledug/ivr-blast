<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\CallLog;
use App\Campaign;
// use App\Contact;

class DashboardController extends Controller
{

    public function __construct() {
        // $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboard.index', compact('campaigns'));
    }

    public function stream()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $MAX_DIAL_TRIES = 3;

        $now = date('Y-m-d');
        $data = array(
            'status' => false,
            'campaigns' => null,
            'calls' => array(),
        );

        $campaigns = Campaign::selectRaw('
                campaigns.id, campaigns.name, campaigns.total_data, campaigns.reference_table
            ')
            ->where('campaigns.status', 1)
            ->whereNull('campaigns.deleted_at')
            ->orderBy('campaigns.id', 'ASC')
            ->get();

        if ($campaigns->count() > 0) {
            $data['status'] = true;
            $data['campaigns'] = $campaigns;
            $data['calls'] = array(
                'call_answered' => 0,
                'call_noanswer' => 0,
                'call_busy' => 0,
                'call_failed' => 0,
                'data_remaining' => 0,
            );

            foreach($data['campaigns'] AS $keyCampaign => $valCampaign) {
                $calls = DB::table($valCampaign->reference_table)
                    ->selectRaw("
                        contacts.id AS cont_id, contacts.call_dial AS cont_call_dial, contacts.call_response AS cont_call_response,
                        contacts.total_calls AS cont_total_calls,
                        (SELECT COUNT(contacts.id) FROM contacts WHERE contacts.campaign_id=" . $valCampaign->id . " AND (contacts.total_calls=" . $MAX_DIAL_TRIES . " OR contacts.call_response='answered')) AS completed_dials,
                        (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='answered' AND call_logs.campaign_id=" . $valCampaign->id . ") AS call_answered,
                        (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='busy' AND call_logs.campaign_id=" . $valCampaign->id . ") AS call_busy,
                        (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='no_answer' AND call_logs.campaign_id=" . $valCampaign->id . ") AS call_noanswer,
                        (SELECT COUNT(call_logs.call_response) FROM call_logs WHERE call_logs.call_response='failed' AND call_logs.campaign_id=" . $valCampaign->id . ") AS call_failed,
                        (SELECT COUNT(call_logs.id) FROM call_logs WHERE call_logs.campaign_id=" . $valCampaign->id . " AND call_logs.call_dial IS NOT NULL) AS dialed_contacts
                    ")
                    ->leftJoin('contacts', $valCampaign->reference_table . '.contact_id', '=', 'contacts.id')
                    ->where($valCampaign->reference_table . '.campaign_id', $valCampaign->id)
                    ->first();

                $data['campaigns'][$keyCampaign]['data_called'] = $calls->completed_dials;
                $data['campaigns'][$keyCampaign]['data_remaining'] = $valCampaign->total_data - $calls->completed_dials;

                $data['calls']['call_answered'] += $calls->call_answered;
                $data['calls']['call_noanswer'] += $calls->call_noanswer;
                $data['calls']['call_busy'] += $calls->call_busy;
                $data['calls']['call_failed'] += $calls->call_failed;
                $data['calls']['data_remaining'] += $valCampaign->total_data - $calls->completed_dials;
            }
        }

        $data = json_encode($data);
        
        echo "data: {$data}\n\nretry:1000\n\n";
        flush();
    }

    /*
    public function stream()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $now = date('Y-m-d');
        
        $data['status'] = false;
        $campaigns = Contact::selectRaw('
                campaigns.name, campaigns.id,
                CAST(SUM(IF(contacts.`call_response` IS NOT NULL, 1, 0)) AS INT) AS data_called,
                CAST(SUM(IF(contacts.`call_response` IS NULL, 1, 0)) AS INT) AS data_remaining
            ')
            ->leftJoin('campaigns','contacts.campaign_id','=','campaigns.id')
            ->where('campaigns.status',1)
            ->groupBy('contacts.campaign_id')
            ->orderBy('campaigns.id', 'ASC')
            ->get();
        
        // $calls = CallLog::selectRaw(
        //     trim(
        //         preg_replace('/\s\s+/', ' ', '
        //             CAST(IFNULL(SUM(IF(call_response="answered", 1, 0)), 0) AS INT) AS call_answered,
        //             CAST(IFNULL(SUM(IF(call_response="no answer", 1, 0)), 0) AS INT) AS call_noanswer,
        //             CAST(IFNULL(SUM(IF(call_response="busy", 1, 0)), 0) AS INT) AS call_busy,
        //             CAST(IFNULL(SUM(IF(call_response="failed", 1, 0)), 0) AS INT) AS call_failed')
        //         )
        //     )
        //     ->whereRaw("DATE(call_dial) = ?", [$now])
        //     ->first();

        $calls = CallLog::selectRaw('
                CAST(IFNULL(SUM(IF(call_response="answered", 1, 0)), 0) AS INT) AS call_answered,
                CAST(IFNULL(SUM(IF(call_response="no_answer", 1, 0)), 0) AS INT) AS call_noanswer,
                CAST(IFNULL(SUM(IF(call_response="busy", 1, 0)), 0) AS INT) AS call_busy,
                CAST(IFNULL(SUM(IF(call_response="failed", 1, 0)), 0) AS INT) AS call_failed
            ')
            ->whereRaw("DATE(call_dial) = ?", [$now])
            ->first();

        if($campaigns->count() > 0){
            $data['status'] = true;
            $data['campaigns'] = $campaigns;
            $data['calls'] = $calls;
        }

        $data = json_encode($data);
        
        echo "data: {$data}\n\nretry:1000\n\n";
        flush();
    }
    */
}
