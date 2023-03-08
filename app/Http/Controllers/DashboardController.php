<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CallLog;
use App\Campaign;
use App\Contact;

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
}
