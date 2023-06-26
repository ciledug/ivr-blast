<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// use App\CallLog;
use App\Campaign;
// use App\Contact;

class DashboardController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboard.index', compact('campaigns'));
    }

    public function stream()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');

        $now = date('Y-m-d');
        $data = array(
            'status' => false,
            'campaigns' => null,
            'calls' => array(),
        );

        $campaigns = Campaign::selectRaw('
                campaigns.id, campaigns.name, campaigns.reference_table, campaigns.total_data
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

            foreach($campaigns AS $keyCampaign => $valCampaign) {
                $calls = DB::table($valCampaign->reference_table)
                    ->selectRaw('
                        CAST(IFNULL(SUM(IF(call_response="answered", 1, 0)), 0) AS INT) AS call_answered,
                        CAST(IFNULL(SUM(IF(call_response="no_answer", 1, 0)), 0) AS INT) AS call_noanswer,
                        CAST(IFNULL(SUM(IF(call_response="busy", 1, 0)), 0) AS INT) AS call_busy,
                        CAST(IFNULL(SUM(IF(call_response="failed", 1, 0)), 0) AS INT) AS call_failed,
                        CAST(SUM(IF(call_response IS NULL, 1, 0)) AS INT) AS data_remaining
                    ')
                    // ->whereRaw("DATE(call_dial) = ?", [$now])
                    ->whereRaw("DATE(call_dial) = ?", ['2023-06-24 00:00:00'])
                    ->first();
                $data['status'] = $calls;

                unset($campaigns[$keyCampaign]['reference_table']);
                $campaigns[$keyCampaign]['data_called'] = $calls->call_answered + $calls->call_noanswer + $calls->call_busy + $calls->call_failed;
                $campaigns[$keyCampaign]['data_remaining'] = $valCampaign->total_data - $campaigns[$keyCampaign]['data_called'];
                // $campaigns[$keyCampaign]['calls'] = $calls;

                $data['calls']['call_answered'] += $calls->call_answered;
                $data['calls']['call_noanswer'] += $calls->call_noanswer;
                $data['calls']['call_busy'] += $calls->call_busy;
                $data['calls']['call_failed'] += $calls->call_failed;
                $data['calls']['data_remaining'] += $calls->data_remaining;
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
