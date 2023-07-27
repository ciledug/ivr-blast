<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;

use App\CallLog;
use App\Campaign;
use App\Contact;

class CallLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('call_logs')->truncate();

        $DUMMY_COUNT = 100;
        $DUMMY_CALL_RECORDING_FILE = 'call_recording.mp3';

        $faker = Faker::create('id_ID');
        $totalCalls = 0;

        $contactId = 0;
        $callDial = '';
        $callConnect = null;
        $callDisconnect = null;
        $callDuration = null;
        $callRecording = null;
        $callResponse = null;

        // ---
        // --- insert dummy for campaign id #2, running-campaign
        // ---
        $campaign = Campaign::select('id', 'reference_table')->where('id', 2)->first();
        $referenceTable = strtolower($campaign->reference_table);
        $randConnect = 0;
        $tempContactCount = 0;
        $tempDialDate = '';

        for ($i=1; $i<=$DUMMY_COUNT; $i++) {
            $callConnect = null;
            $callDisconnect = null;
            $callDuration = null;
            $callResponse = null;
            $callRecording = null;

            $contactId = $faker->numberBetween(1, 100);
            $callResponse = 'failed';

            $contact = Contact::select(
                    'contacts.id AS con_id', 'contacts.total_calls AS con_total_calls', 'contacts.call_response AS con_call_response',
                    'contacts.call_dial AS con_call_dial',
                    $referenceTable . '.due_date AS con_due_date' 
                )
                ->leftJoin($referenceTable, 'contacts.id', '=', $referenceTable. '.contact_id')
                ->where($referenceTable . '.id', $contactId)
                ->get();
            // dd($contact);
            $tempContactCount = $contact->count();

            if (($tempContactCount <= 3) && ($contact[$tempContactCount - 1]->con_call_response !== 'answered')) {
                $contact = $contact[$tempContactCount - 1];

                $tempDialDate = $contact->con_due_date . ' ' . date('H:i:s');
                if ($contact->con_call_dial) {
                    $tempDialDate = Carbon::createFromFormat('Y-m-d H:i:s', $contact->con_call_dial)->format('Y-m-d') . ' ' . date('H:i:s');
                }

                $callDial = Carbon::createFromFormat('Y-m-d H:i:s', $tempDialDate)->addHours($tempContactCount);
                $randConnect = $faker->numberBetween(0, 20);
        
                if ($randConnect % 2 == 0) {
                    $callConnect = Carbon::createFromFormat('Y-m-d H:i:s', $callDial)->addSeconds($faker->numberBetween(1, 21));
                    $callResponse = (($faker->numberBetween(1, 2) % 2) == 0) ? 'no_answer' : 'busy';
    
                    if ($randConnect % 4 == 0) {
                        $callDisconnect = Carbon::createFromFormat('Y-m-d H:i:s', $callConnect)->addSeconds($faker->numberBetween(5, 31));
                        $callDuration = $callDisconnect->diffInSeconds($callConnect);
    
                        $callResponse = 'answered';
                        $callRecording = $DUMMY_CALL_RECORDING_FILE;
                    }
                }
                
                CallLog::insert([
                    'campaign_id' => $campaign->id,
                    'contact_id' => $contact->con_id,
                    'call_dial' => $callDial->format('Y-m-d H:i:s'),
                    'call_connect' => empty($callConnect) ? null : $callConnect->format('Y-m-d H:i:s'),
                    'call_disconnect' => empty($callDisconnect) ? null : $callDisconnect->format('Y-m-d H:i:s'),
                    'call_duration' => $callDuration,
                    'call_recording' => $callRecording,
                    'call_response' => $callResponse,
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);

                $contact->id = $contact->con_id;
                $contact->total_calls = is_numeric($contact->con_total_calls) ? $contact->con_total_calls + 1 : 1;
                $contact->call_dial = $callDial->format('Y-m-d H:i:s');
                $contact->call_response = !empty($callResponse) ? $callResponse : null;
                $contact->save();
            }
            else {
                $i--;
            }
            
            $contact = null;
            unset($contact);
        }
        
        
        // ---
        // --- insert dummy for campaign id #3, finished-campaign
        // ---
        $campaign = Campaign::select('id', 'reference_table')->where('id', 3)->first();
        $referenceTable = strtolower($campaign->reference_table);
        $contacts = Contact::select(
                'contacts.id AS cont_id', 'contacts.call_dial'
            )
            ->leftJoin($referenceTable, 'contacts.id', '=', $referenceTable . '.contact_id')
            ->where('contacts.campaign_id', $campaign->id)
            ->get();
        $isDummyFinished = false;

        foreach ($contacts AS $keyContact => $valContact) {
            $callDial = ($valContact->call_dial == null) ? Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')) : $valContact->call_dial;
            $callConnect = null;
            $callDisconnect = null;
            $callDuration = null;
            $callResponse = 'failed';
            $callRecording = null;
            $isDummyFinished = false;

            for ($i=1; $i<=3; $i++) {
                if (!$isDummyFinished) {
                    $callDial = $callDial->addMinutes($faker->numberBetween(60, 121));
                    $randConnect = $faker->numberBetween(0, 10);
        
                    if ($randConnect % 2 == 0) {
                        $callConnect = Carbon::createFromFormat('Y-m-d H:i:s', $callDial)->addSeconds($faker->numberBetween(1, 21));
                        $callResponse = (($faker->numberBetween(1, 2) % 2) == 0) ? 'no_answer' : 'busy';
        
                        if ($randConnect % 4 == 0) {
                            $callDisconnect = Carbon::createFromFormat('Y-m-d H:i:s', $callConnect)->addSeconds($faker->numberBetween(5, 31));
                            $callDuration = $callDisconnect->diffInSeconds($callConnect);
        
                            $callResponse = 'answered';
                            $callRecording = $DUMMY_CALL_RECORDING_FILE;

                            $isDummyFinished = true;
                        }
                    }

                    CallLog::insert([
                        'campaign_id' => $campaign->id,
                        'contact_id' => $valContact->cont_id,
                        'call_dial' => $callDial->format('Y-m-d H:i:s'),
                        'call_connect' => empty($callConnect) ? null : $callConnect->format('Y-m-d H:i:s'),
                        'call_disconnect' => empty($callDisconnect) ? null : $callDisconnect->format('Y-m-d H:i:s'),
                        'call_duration' => $callDuration,
                        'call_recording' => $callRecording,
                        'call_response' => $callResponse,
                        'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    ]);

                    $valContact->id = $valContact->cont_id;
                    $valContact->total_calls = $i;
                    $valContact->call_dial = $callDial->format('Y-m-d H:i:s');
                    $valContact->call_response = $callResponse;
                    $valContact->save();
                }
            }
        }
    }
}
