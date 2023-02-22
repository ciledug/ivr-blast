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
        $campaignList = array();
        $campaignsData = Campaign::where('status', '=', 1)
            ->get();

        $totalAnswered = 0;
        $totalNoAnswer = 0;
        $totalBusy = 0;
        $totalFailed = 0;

        foreach($campaignsData AS $keyCampaign => $valueCampaign) {
            $contactsData = Contact::where('campaign_id', '=', $valueCampaign->id)->get();

            $answered = 0;
            $noAnswer = 0;
            $busy = 0;
            $failed = 0;

            foreach($contactsData AS $keyContact => $valueContact) {
                $callLogData = CallLog::select('call_response')
                    ->where('contact_id', '=', $valueContact->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($callLogData) {
                    if ($callLogData->call_response == 'answered') $answered++;
                    else if ($callLogData->call_response == 'no_answer') $noAnswer++;
                    else if ($callLogData->call_response == 'busy') $busy++;
                    else if ($callLogData->call_response == 'failed') $failed++;
                }
            }

            $totalContactsData = $contactsData->count();
            $totalProgress = (($answered + $noAnswer + $busy + $failed) / $totalContactsData) * 100;

            $campaignList[] = array(
                'name' => $valueCampaign->name,
                'progress' => number_format($totalProgress, 2, ',', '.'),
            );

            $totalAnswered += $answered;
            $totalNoAnswer += $noAnswer;
            $totalBusy += $busy;
            $totalFailed += $failed;
        }

        $data = array(
            'campaigns' => $campaignList,
            'answered' => $totalAnswered,
            'noanswer' => $totalNoAnswer,
            'busy' => $totalBusy,
            'failed' => $totalFailed,
        );

        // dd($data['campaigns']);

        return view('dashboard.index', $data);
    }
}
