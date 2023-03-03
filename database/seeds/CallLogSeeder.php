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

        $DUMMY_COUNT = 1000;
        $DUMMY_CALL_RECORDING_FILE = 'call_recording.mp3';

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

            $contactId = $faker->numberBetween(1, 18100);
            $callDial = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
            $callResponse = 'failed';

            $randConnect = $faker->numberBetween(0, 10);
    
            if ($randConnect % 2 == 0) {
                $callConnect = Carbon::now('Asia/Jakarta')->addSeconds($faker->numberBetween(1, 61));
                $callResponse = (($faker->numberBetween(1, 2) % 2) == 0) ? 'no_answer' : 'busy';

                if ($randConnect % 4 == 0) {
                    $callDisconnect = Carbon::now('Asia/Jakarta')->addSeconds($faker->numberBetween(60, 121));
                    $callDuration = $callDisconnect->diffInSeconds($callConnect);
                    $callDisconnect = $callDisconnect->format('Y-m-d H:i:s');

                    $callResponse = 'answered';
                    $callRecording = $DUMMY_CALL_RECORDING_FILE;
                }

                $callConnect = $callConnect->format('Y-m-d H:i:s');
            }

            DB::table('call_logs')->insert([
                'contact_id' => $contactId,
                'call_dial' => $callDial,
                'call_connect' => $callConnect,
                'call_disconnect' => $callDisconnect,
                'call_duration' => $callDuration,
                'call_response' => $callResponse,
                'call_recording' => $callRecording,
            ]);

            $contact = Contact::select('id', 'name', 'total_calls', 'call_dial', 'call_response')
                ->where('id', '=', $contactId)
                ->first();

            $contact->total_calls = is_numeric($contact->total_calls) ? $contact->total_calls + 1 : 1;
            $contact->call_dial = !empty($callDial) ? $callDial : null;
            $contact->call_response = !empty($callResponse) ? $callResponse : null;
            $contact->save();
        }
    }
}
