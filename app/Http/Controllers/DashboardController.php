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

    public function index() {
        // $campaignsData = Campaign::where('status', '=', 1)->get();
        $campaignsData = Campaign::select('id', 'name', 'total_data')
            ->where('status', '=', 1)
            ->paginate(15);

        $totalAnswered = 0;
        $totalNoAnswer = 0;
        $totalBusy = 0;
        $totalFailed = 0;

        foreach($campaignsData AS $keyCampaign => $valueCampaign) {
            $answered = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'answered')->count();
            $noAnswer = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'no_answer')->count();
            $busy = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'busy')->count();
            $failed = Contact::where('campaign_id', '=', $valueCampaign->id)->where('call_response', '=', 'failed')->count();

            $progress = (($answered + $noAnswer + $busy + $failed) / $valueCampaign->total_data) * 100;
            $campaignsData[$keyCampaign]['progress'] = $progress;

            $totalAnswered += $answered;
            $totalNoAnswer += $noAnswer;
            $totalBusy += $busy;
            $totalFailed += $failed;
        }

        $data = array(
            'campaigns' => $campaignsData,
            'answered' => $totalAnswered,
            'noanswer' => $totalNoAnswer,
            'busy' => $totalBusy,
            'failed' => $totalFailed,
        );

        return view('dashboard.index', $data);
    }
}
