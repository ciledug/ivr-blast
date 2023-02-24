<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\CallLog;
use App\Campaign;
use Carbon\Carbon;
use Validator;

class CallLogController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index($campaign=null)
    {
        $campaigns = Campaign::select('id', 'unique_key', 'name')->get();
        $callLogs = array();

        $validator = Validator::make(['campaign' => $campaign], [
            'campaign' => 'nullable|numeric|min:1|max:' . $campaigns->count()
        ]);
        if ($validator->fails()) return back();

        $callLogs = CallLog::select(
            'contacts.id AS contact_id', 'contacts.name',
                'call_logs.call_dial', 'call_logs.call_connect', 'call_logs.call_disconnect', 'call_logs.call_duration', 'call_logs.call_response', 'call_logs.call_recording'
            )
            ->leftJoin('contacts', 'call_logs.contact_id', '=', 'contacts.id')
            ->orderBy('call_logs.id', 'DESC');

        if ($campaign) {
            $callLogs->where('contacts.campaign_id', '=', $campaign);
        }

        $callLogs = $callLogs->paginate(15);

        return view('calllogs.index', array(
            'campaigns' => $campaigns,
            'selectedCampaign' => (int) $campaign,
            'calllogs' => $callLogs,
        ));
    }

    public function getCallStatus(Request $request, $sentStartDate=null, $sentEndDate=null) {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $returnedCode = 500;

        $queryCallLog = CallLog::distinct()->orderBy('call_response', 'ASC')->get(['call_response']);

        if ($queryCallLog) {
            // dd($queryCallLog->toArray());

            if ($sentStartDate != null) {
                $startDate = Carbon::parse($sentStartDate, 'Asia/Jakarta');
                if (!$startDate) {
                    $startDate = Carbon::now('Asia/Jakarta');
                }
            }
    
            if ($sentEndDate != null) {
                $endDate = Carbon::parse($sentEndDate, 'Asia/Jakarta');
                if (!$endDate) {
                    $endDate = Carbon::now('Asia/Jakarta');
                }
            }
    
            $startDate = $startDate->format('Y-m-d') . ' 00:00:00';
            $endDate = $endDate->format('Y-m-d') . ' 23:59:59';
            // echo 'startDate: ' . $startDate . ' endDate: ' . $endDate . ' ';
            $startDate = '2023-01-23 00:00:00';
            $endDate = '2023-01-24 23:59:59';

            $tempResult = array();
            foreach($queryCallLog->toArray() AS $keyResponseCode => $valueResponseCode) {
                $query = CallLog::where('call_response', '=', $valueResponseCode['call_response'])
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('contact_id')
                    ->orderBy('created_at', 'DESC')
                    ->get();
                $tempResult[] = array(
                    'cr_' . $valueResponseCode['call_response'] => $query->count()
                );
            }

            $returnedCode = 200;
        }
        
        $returnedResponse = array(
            'code' => $returnedCode,
            'data' => $tempResult,
        );

        return response()->json($returnedResponse);
    }
}
