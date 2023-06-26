<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\Contact;
use App\CallLog;
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

    /*
    public function show(Request $request, $id)
    {
        $contact = Contact::find($id);
        $dataLogs = CallLog::where('contact_id', $contact->id)->orderBy('id', 'ASC')->get();

        return view('contact.show', compact('contact', 'dataLogs'));
    }
    */

    public function show(Request $request, $id, $campaignId)
    {
        $campaignHeaders = Campaign::select(
                'campaigns.template_id', 'campaigns.reference_table',
                'template_headers.name AS templ_header_name', 'template_headers.column_type AS templ_column_type'
            )
            ->leftJoin('template_headers', 'campaigns.template_id', '=', 'template_headers.template_id')
            ->where('campaigns.id', $campaignId)
            ->whereNull('campaigns.deleted_at')
            ->get();
        // dd($campaignHeaders);

        $referenceTable = $campaignHeaders[0]->reference_table;

        $contact = DB::table($referenceTable)
            ->select($referenceTable . '.*', $referenceTable . '_call_logs.*', $referenceTable . '_call_logs.created_at AS call_logs_created_at')
            ->leftJoin($referenceTable . '_call_logs', $referenceTable . '.id', '=', $referenceTable . '_call_logs.contact_id')
            ->where($referenceTable . '.id', $id)
            ->get();
        // dd($contact);

        return view('contact.show', [
            'campaign_headers' => $campaignHeaders,
            'contact' => $contact,
        ]);
    }

    public function contactList(Request $request, $campaign) {
        $campaign = Str::replaceFirst('_', '', $campaign);

        $returnedCode = 500;
        $command = Contact::select(DB::raw('
                account_id,
                contacts.name AS name,
                phone,
                DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                IF (total_calls IS NULL, 0, total_calls) AS total_calls,
                FORMAT(nominal, 0) AS nominal,
                DATE_FORMAT(call_dial, "%d/%m/%Y %H:%i:%s") AS call_dial,
                CONCAT(UCASE(LEFT(call_response, 1)), SUBSTRING(call_response, 2)) AS call_response
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
        $ORDERED_COLUMNS = ['account_id', 'name', 'phone', 'bill_date', 'due_date', 'total_calls', 'nominal', 'call_dial', 'call_response'];
        $ORDERED_BY = ['desc', 'asc'];
        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? (int) $request->start : 0;
        $LENGTH = is_numeric($request->length) ? (int) $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $campaign = Str::replaceFirst('_', '', $request->campaign);
        $campaign = Campaign::where('unique_key', '=', $campaign)->first();

        $recordsTotalQuery = 0;
        $contactList = [];

        if ($campaign) {
            $query = Contact::select(DB::raw('
                    id,
                    account_id,
                    contacts.name AS name,
                    phone,
                    DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                    DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                    IF (total_calls IS NULL, 0, total_calls) AS total_calls,
                    FORMAT(nominal, 0) AS nominal
                '))
                ->where('campaign_id', '=', $campaign->id);

            if (!empty($SEARCH_VALUE)) {
                $query->where(function($q) use($SEARCH_VALUE) {
                    $q->where('contacts.account_id', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orWhere('contacts.name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('contacts.phone', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('bill_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('due_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('nominal', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_dial', 'LIKE', '%' . $SEARCH_VALUE . '%');
                });
            }

            if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
                $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
            }
            $filteredData = $query->get();
            $contactList = $query->offset($START)->limit($LENGTH)->get();

            foreach ($contactList AS $keyContact => $valueContact) {
                $contactList[$keyContact]->call_dial = '-';
                $contactList[$keyContact]->call_response = '-';
                $tempCallLog = CallLog::select('call_dial', 'call_response')
                    ->where('contact_id', '=', $valueContact->id)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($tempCallLog) {
                    $contactList[$keyContact]->call_dial = $tempCallLog->call_dial;
                    $contactList[$keyContact]->call_response = ucwords($tempCallLog->call_response);
                }
            }
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => Contact::where('contacts.campaign_id', '=', $campaign->id)->count(),
            'recordsFiltered' => $filteredData->count(),
            'data' => $contactList
        );

        return response()->json($returnedResponse);
    }

    public function getContactListCommon(Request $request) {
        $ORDERED_COLUMNS = ['account_id', 'name', 'phone', 'bill_date', 'due_date', 'total_calls', 'nominal', 'call_dial', 'call_response'];
        $ORDERED_BY = ['desc', 'asc'];

        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? (int) $request->start : 0;
        $LENGTH = is_numeric($request->length) ? (int) $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $campaign = Campaign::where('unique_key', '=', Str::replaceFirst('_', '', $request->campaign))->first();

        $recordsTotalQuery = 0;
        $contactList = [];

        if ($campaign) {
            $query = Contact::select(DB::raw('
                    id,
                    account_id,
                    contacts.name AS name,
                    phone,
                    DATE_FORMAT(bill_date, "%d/%m/%Y") AS bill_date,
                    DATE_FORMAT(due_date, "%d/%m/%Y") AS due_date,
                    IF (total_calls IS NULL, 0, total_calls) AS total_calls,
                    FORMAT(nominal, 0) AS nominal
                '))
                ->where('campaign_id', '=', $campaign->id);

            if (!empty($SEARCH_VALUE)) {
                $query->where(function($q) use($SEARCH_VALUE) {
                    $q->where('contacts.account_id', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orWhere('contacts.name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('contacts.phone', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('bill_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('due_date', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('nominal', 'LIKE', '%' . $SEARCH_VALUE . '%')
                        ->orwhere('call_dial', 'LIKE', '%' . $SEARCH_VALUE . '%');
                });
            }

            if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
                $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
            }
            $filteredData = $query->get();
            // $contactList = $query->offset($START)->limit($LENGTH)->get();
            $contactList = $query->paginate(15);

            foreach ($contactList AS $keyContact => $valueContact) {
                $contactList[$keyContact]->call_dial = '-';
                $contactList[$keyContact]->call_response = '-';
                $tempCallLog = CallLog::select('call_dial', 'call_response')
                    ->where('contact_id', '=', $valueContact->id)
                    ->orderBy('id', 'DESC')
                    ->first();
                if ($tempCallLog) {
                    $contactList[$keyContact]->call_dial = $tempCallLog->call_dial;
                    $contactList[$keyContact]->call_response = ucwords($tempCallLog->call_response);
                }
            }
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => Contact::where('contacts.campaign_id', '=', $campaign->id)->count(),
            'recordsFiltered' => $filteredData->count(),
            'data' => $contactList
        );

        return $returnedResponse;
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
