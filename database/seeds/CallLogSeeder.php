<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;
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
        $callResponse = null;
        $callRecording = null;

        // ---
        // --- insert dummy for campaign-id 2, running campaign
        // ---
        $campaign = Campaign::select('reference_table')->where('id', 2)->first();
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

            $contact = DB::table($referenceTable)->select(
                    $referenceTable . '.id', $referenceTable . '.due_date', $referenceTable . '.total_calls',
                    $referenceTable . '_call_logs.call_dial', $referenceTable . '_call_logs.call_response'
                )
                ->leftJoin($referenceTable . '_call_logs', $referenceTable . '.id', '=', $referenceTable . '_call_logs.contact_id')
                ->where($referenceTable . '.id', $contactId)
                ->get();
            // dd($contact);
            $tempContactCount = $contact->count();

            if (($tempContactCount < 3) && ($contact[$tempContactCount - 1]->call_response !== 'answered')) {
                $contact = $contact[$tempContactCount - 1];

                $tempDialDate = $contact->due_date . ' ' . date('H:i:s');
                if ($contact->call_dial) {
                    $tempDialDate = Carbon::createFromFormat('Y-m-d H:i:s', $contact->call_dial)->format('Y-m-d') . ' ' . date('H:i:s');
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
                
                DB::table($referenceTable . '_call_logs')->insert([
                    'contact_id' => $contact->id,
                    'call_dial' => $callDial->format('Y-m-d H:i:s'),
                    'call_connect' => empty($callConnect) ? null : $callConnect->format('Y-m-d H:i:s'),
                    'call_disconnect' => empty($callDisconnect) ? null : $callDisconnect->format('Y-m-d H:i:s'),
                    'call_duration' => $callDuration,
                    'call_response' => $callResponse,
                    'call_recording' => $callRecording,
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);

                DB::table($referenceTable)
                    ->where('id', $contact->id)
                    ->update([
                        'total_calls' => is_numeric($contact->total_calls) ? $contact->total_calls + 1 : 1,
                        'call_dial' => $callDial->format('Y-m-d H:i:s'),
                        'call_response' => !empty($callResponse) ? $callResponse : null,
                    ]);
            }
            else {
                $i--;
            }
            
            $contact = null;
            unset($contact);
        }
        
        
        // ---
        // --- insert dummy for campaign-id 3, finished campaign
        // ---
        $campaign = Campaign::where('id', 3)->first();
        $referenceTable = strtolower($campaign->reference_table);
        $contacts = DB::table($referenceTable)->get();
        $isDummyFinished = false;

        foreach ($contacts AS $keyContact => $valContact) {
            $contactId = $faker->numberBetween(1, 100);
            $callDial = '';
            $callConnect = null;
            $callDisconnect = null;
            $callDuration = null;
            $callResponse = 'failed';
            $callRecording = null;
            $isDummyFinished = false;

            for ($i=1; $i<=3; $i++) {
                if (!$isDummyFinished) {
                    $callDial = Carbon::createFromFormat('Y-m-d H:i:s', $valContact->due_date . ' ' . date('H:i:s'))->addSeconds($faker->numberBetween(1, 21));
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

                    DB::table($referenceTable . '_call_logs')->insert([
                        'contact_id' => $valContact->id,
                        'call_dial' => $callDial->format('Y-m-d H:i:s'),
                        'call_connect' => empty($callConnect) ? null : $callConnect->format('Y-m-d H:i:s'),
                        'call_disconnect' => empty($callDisconnect) ? null : $callDisconnect->format('Y-m-d H:i:s'),
                        'call_duration' => $callDuration,
                        'call_response' => $callResponse,
                        'call_recording' => $callRecording,
                        'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    ]);
        
                    DB::table($referenceTable)
                        ->where('id', $valContact->id)
                        ->update([
                            'total_calls' => $i,
                            'call_dial' => $callDial->format('Y-m-d H:i:s'),
                            'call_response' => $callResponse,
                        ]);
                }
            }
        }
    }
}
