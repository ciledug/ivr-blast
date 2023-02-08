<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Contact;
use Carbon\Carbon;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
    }

    public function show(Request $request)
    {
        $data = array();

        $contact = Contact::where('account_id', '=', Str::replaceFirst('_', '', $request->contact))
            ->whereNull('deleted_at')
            ->first();

        if ($contact) {
            $data['contact'] = $contact;
        }

        return view('contact.show', $data);
    }

    public function contactList(Request $request, $campaign) {
        $campaign = Str::replaceFirst('_', '', $campaign);

        $returnedCode = 500;
        $command = Contact::select(DB::raw('
                account_id, contacts.name AS name, phone,
                DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                FORMAT(nominal, 0) AS nominal,
                DATE_FORMAT(call_dial, "%d/%m/%Y %H:%i:%s") AS call_dial,
                DATE_FORMAT(call_connect, "%d/%m/%Y %H:%i:%s") AS call_connect,
                DATE_FORMAT(call_disconnect, "%d/%m/%Y %H:%i:%s") AS call_disconnect,
                DATE_FORMAT(call_duration, "%d/%m/%Y %H:%i:%s") AS call_duration,
                IF(call_response=0, "Answered",
                  IF(call_response=1, "No Answer",
                    IF(call_response=2, "Busy",
                      IF(call_response=3, "Failed", NULL)
                    )
                  )
                ) AS call_response
            '))
            ->leftJoin('campaigns', 'campaign_id', '=', 'campaigns.id')
            ->where('campaigns.unique_key', $campaign);

        $contactList = array();

        $returnedResponse = array(
            'code' => $returnedCode,
            'data' => $command->get()
        );

        return response()->json($returnedResponse);
    }
    
    public function getCallStatus(Request $request, $sentStartDate=null, $sentEndDate=null) {
        $startDate = Carbon::now('Asia/Jakarta');
        $endDate = Carbon::now('Asia/Jakarta');
        $returnedCode = 500;

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
        echo 'startDate: ' . $startDate . ' endDate: ' . $endDate . ' ';

        $query = Contact::select(DB::raw('
                (SELECT COUNT(call_response) FROM contacts WHERE created_at BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\' AND call_response=0) AS answered,
                (SELECT COUNT(call_response) FROM contacts WHERE created_at BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\' AND call_response=1) AS no_answer,
                (SELECT COUNT(call_response) FROM contacts WHERE created_at BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\' AND call_response=2) AS busy,
                (SELECT COUNT(call_response) FROM contacts WHERE created_at BETWEEN \'' . $startDate . '\' AND \'' . $endDate . '\' AND call_response=3) AS failed
            '))
            ->get();
        
        $returnedResponse = array(
            'code' => $returnedCode,
            'data' => $query,
        );

        return response()->json($returnedResponse);
    }
}
