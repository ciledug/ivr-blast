<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Campaign;
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

    public function show(Request $request, $contact=null, $campaign=null)
    {
        $data = array(
            'contact' => array()
        );

        $contact = Contact::select([
                'contacts.id', 'contacts.account_id', 'contacts.name', 'contacts.phone', 'contacts.bill_date', 'contacts.due_date', 'contacts.nominal',
                'call_logs.call_dial', 'call_logs.call_connect', 'call_logs.call_connect', 'call_logs.call_disconnect',
                'call_logs.call_duration', 'call_logs.call_response'
            ])
            ->leftJoin('call_logs', 'call_logs.contact_id', '=', 'contacts.id')
            ->where('contacts.campaign_id', '=', $campaign)
            ->where('contacts.account_id', '=', Str::replaceFirst('_', '', $contact))
            ->orderBy('call_logs.created_at', 'DESC')
            ->first();

        if ($contact) {
            $contact->nominal = number_format($contact->nominal, 0, ',', '.');
            $contact->call_response = ucwords($contact->call_response);
            $data['contact'] = $contact;
        }
        
        // dd($data);
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

    public function contactListAjax(Request $request) {
        $ORDERED_COLUMNS = ['account_id', 'name', 'phone', 'bill_date', 'due_date', 'nominal', 'call_dial', 'call_response'];
        $ORDERED_BY = ['desc', 'asc'];
        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? $request->start : 0;
        $LENGTH = is_numeric($request->length) ? $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $campaign = Str::replaceFirst('_', '', $request->campaign);
        $campaign = Campaign::where('unique_key', '=', $campaign)->first();

        $recordsTotalQuery = 0;
        $contactList = [];

        if ($campaign) {
            $query = Contact::select(DB::raw('
                    account_id, contacts.name AS name, phone,
                    DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                    DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                    FORMAT(nominal, 0) AS nominal,
                    DATE_FORMAT(call_dial, "%d/%m/%Y %H:%i:%s") AS call_dial,
                    DATE_FORMAT(call_connect, "%d/%m/%Y %H:%i:%s") AS call_connect,
                    DATE_FORMAT(call_disconnect, "%d/%m/%Y %H:%i:%s") AS call_disconnect,
                    DATE_FORMAT(call_duration, "%d/%m/%Y %H:%i:%s") AS call_duration,
                    CONCAT(UCASE(LEFT(call_response, 1)), SUBSTRING(call_response, 2)) AS call_response
                '))
                ->offset($START)
                ->limit($LENGTH)
                ->where('campaign_id', '=', $campaign->id);

            if (!empty($SEARCH_VALUE)) {
                $query->where(function($q) use($SEARCH_VALUE) {
                    $q->where('contacts.account_id', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orWhere('contacts.name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('contacts.phone', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('bill_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('due_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('nominal', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_dial', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_connect', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_disconnect', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_duration', 'LIKE', '%' . $SEARCH_VALUE . '%');
                });
            }

            if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
                $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
            }
            
            $contactList = $query->get();
            $recordsTotalQuery = Contact::where('contacts.campaign_id', '=', $campaign->id)->count();
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => $contactList->count(),
            'recordsFiltered' => $recordsTotalQuery,
            'data' => $contactList
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
