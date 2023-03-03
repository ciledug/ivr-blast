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
        $campaigns = Contact::selectRaw('campaigns.name, campaigns.id, campaigns.unique_key, CAST(SUM(IF(contacts.`call_response` IS NOT NULL,1,0)) AS INT) AS data_called, CAST(SUM(IF(contacts.`call_response` IS NULL,1,0)) AS INT) AS data_remaining')
                            ->leftJoin('campaigns','contacts.campaign_id','=','campaigns.id')
                            ->where('campaigns.status',1)
                            ->whereNull('deleted_at')
                            ->groupBy('contacts.campaign_id')
                            ->orderBy('campaigns.id', 'ASC')
                            ->get();
        
        $calls = CallLog::selectRaw(trim(preg_replace('/\s\s+/', ' ','
                                CAST(IFNULL(SUM(IF(call_response="ANSWERED",1,0)),0) AS INT) AS call_answered,
                                CAST(IFNULL(SUM(IF(call_response="NO ANSWER",1,0)),0) AS INT) AS call_noanswer,
                                CAST(IFNULL(SUM(IF(call_response="BUSY",1,0)),0) AS INT) AS call_busy,
                                CAST(IFNULL(SUM(IF(call_response="FAILED",1,0)),0) AS INT) AS call_failed')))
                            ->whereRaw('DATE(call_dial) = ?',[$now])
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

    // public function index() {
    //     // $campaignsData = Campaign::where('status', '=', 1)->get();
    //     $campaignsData = Campaign::select('id', 'name', 'total_data')
    //         ->where('status', '=', 1)
    //         ->paginate(15);

    //     $totalAnswered = 0;
    //     $totalNoAnswer = 0;
    //     $totalBusy = 0;
    //     $totalFailed = 0;

    //     foreach($campaignsData AS $keyCampaign => $valueCampaign) {
    //         $answered = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'answered')->count();
    //         $noAnswer = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'no_answer')->count();
    //         $busy = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'busy')->count();
    //         $failed = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'failed')->count();

    //         $progress = (($answered + $noAnswer + $busy + $failed) / $valueCampaign->total_data) * 100;
    //         $campaignsData[$keyCampaign]['progress'] = $progress;

    //         $totalAnswered += $answered;
    //         $totalNoAnswer += $noAnswer;
    //         $totalBusy += $busy;
    //         $totalFailed += $failed;
    //     }

    //     $data = array(
    //         'campaigns' => $campaignsData,
    //         'answered' => $totalAnswered,
    //         'noanswer' => $totalNoAnswer,
    //         'busy' => $totalBusy,
    //         'failed' => $totalFailed,
    //     );

    //     return view('dashboard.index', $data);
    // }
}
