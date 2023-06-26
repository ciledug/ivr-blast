<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;
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
        $REFERENCE_TABLE = 'c_demo_t_demo_0000001';

        $faker = Faker::create('id_ID');
        $contactId = 0;
        $callDial = '';
        $callConnect = null;
        $callDisconnect = null;
        $callDuration = null;
        $callResponse = null;
        $callRecording = null;

        for ($i = 1; $i <= $DUMMY_COUNT; $i++) {
            $callConnect = null;
            $callDisconnect = null;
            $callDuration = null;
            $callResponse = null;
            $callRecording = null;

            $contactId = $faker->numberBetween(1, 100);
            $callResponse = 'failed';

            $contact = DB::table($REFERENCE_TABLE)
                ->select('id', 'due_date', 'total_calls', 'call_response')
                ->where('id', $contactId)
                ->first();
            // dd($contact);

            if ($contact->call_response !== 'answered') {
                $callDial = Carbon::createFromFormat('Y-m-d H:i:s', $contact->due_date . ' ' . date('H:i:s'))->addSeconds($faker->numberBetween(1, 21));
                $randConnect = $faker->numberBetween(0, 10);
        
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
    
                DB::table($REFERENCE_TABLE . '_call_logs')->insert([
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

                DB::table($REFERENCE_TABLE)
                    ->where('id', $contact->id)
                    ->update([
                        'total_calls' => is_numeric($contact->total_calls) ? $contact->total_calls + 1 : 1,
                        'call_dial' => $callDial->format('Y-m-d H:i:s'),
                        'call_response' => !empty($callResponse) ? $callResponse : null,
                    ]);
            }
        }
    }
}
