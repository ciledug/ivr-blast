<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    /*
    public function run()
    {
        DB::table('campaigns')->truncate();

        $referenceTable = 'c_demo_t_demo_0000001';

        Schema::dropIfExists($referenceTable);
        Schema::dropIfExists($referenceTable . '_call_logs');

        $demoCampaign = [
            'name' => 'Demo Campaign',
            'text_voice' => '',
            'voice_gender' => 'female_normal',
        ];
        $demoCampaign['text_voice'] = 'Notifikasi ini sebagai pengingat lebih awal pembayaran angsuran <voice-1> Anda untuk nomor perjanjian xxxxxxxxxxxx dengan ';
        $demoCampaign['text_voice'] .= 'nominal sebesar Rp <voice-2> jatuh tempo pada <voice-3> yang sudah dapat dilakukan pembayaran melalui Kantor POS, ';
        $demoCampaign['text_voice'] .= 'Indomaret, Alfamart, Kantor jaringan BAF, Tokopedia, ATM BCA  dan Mandiri terdekat.';

        // ---
        // --- insert campaign info into 'campaigns' table
        // ---
        DB::table('campaigns')
            ->insert([
                'unique_key' => Carbon::now('Asia/Jakarta')->getTimestamp(),
                'name' => $demoCampaign['name'],
                'created_by' => 2,
                'template_id' => 2,
                'reference_table' => $referenceTable,
                'text_voice' => $demoCampaign['text_voice'],
                'voice_gender' => $demoCampaign['voice_gender'],
                'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s')
            ]);

        // ---
        // --- create reference contacts table
        // ---
        Schema::create($referenceTable, function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('product_name', 30);
            $table->string('phone', 15);
            $table->date('due_date');
            $table->integer('nominal')->unsigned();
            $table->integer('extension')->unsigned()->nullable();
            $table->string('callerid')->nullable();
            $table->string('voice')->nullable();
            $table->tinyInteger('total_calls')->unsigned()->nullable();
            $table->datetime('call_dial')->index()->nullable();
            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
            $table->timestamps();
            $table->softDeletes();
        });

        // ---
        // --- create reference contact logs table
        // ---
        Schema::create($referenceTable . '_call_logs', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->datetime('call_dial')->nullable();
            $table->datetime('call_connect')->nullable();
            $table->datetime('call_disconnect')->nullable();
            $table->integer('call_duration')->default(0)->nullable();
            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
            $table->string('call_recording', 255)->nullable();
            $table->timestamps();
        });
    }
    */

    public function run()
    {
        DB::table('campaigns')->truncate();

        $demoCampaigns = array(
            [
                'name' => 'Demo Ready Campaign',
                'template_id' => 1,
                'status' => 0,
                'voice_gender' => 'female_normal',
                'text_voice' => 'Notifikasi ini sebagai pengingat lebih awal pembayaran angsuran <voice-1> Anda untuk nomor perjanjian xxxxxxxxxxxx dengan nominal sebesar Rp <voice-2> jatuh tempo pada <voice-3> yang sudah dapat dilakukan pembayaran melalui Kantor POS, Indomaret, Alfamart, Kantor jaringan BAF, Tokopedia, ATM BCA  dan Mandiri terdekat.',
                'reference_table' => 'c_demo_t_demo_0000001',
            ],
            [
                'name' => 'Demo Running Campaign',
                'template_id' => 2,
                'status' => 1,
                'voice_gender' => 'male_strong',
                'text_voice' => 'Berdasarkan catatan kami, saat ini tagihan angsuran <voice-1> Anda untuk nomor perjanjian xxxxxxxxxxxx dengan nominal sebesar Rp <voice-2> sudah jatuh tempo pada <voice-3>. Segera lakukan pembayaran angsuran BAF hari ini melalui Alfamart, kantor jaringan BAF, Tokopedia, ATM BCA dan tempat pembayaran lainnya yang bekerja sama dengan BAF.',
                'reference_table' => 'c_demo_t_demo_0000002',
            ],
            [
                'name' => 'Demo Finished Campaign',
                'template_id' => 2,
                'status' => 3,
                'voice_gender' => 'female_normal',
                'text_voice' => 'Notifikasi ini sebagai pengingat lebih awal pembayaran angsuran <voice-1> Anda untuk nomor perjanjian xxxxxxxxxxxx dengan nominal sebesar Rp <voice-2> jatuh tempo pada <voice-3> yang sudah dapat dilakukan pembayaran melalui Kantor POS, Indomaret, Alfamart, Kantor jaringan BAF, Tokopedia, ATM BCA dan Mandiri terdekat.',
                'reference_table' => 'c_demo_t_demo_0000003',
            ],
        );

        foreach ($demoCampaigns AS $keyDemo => $valDemo) {
            $referenceTable = strtolower($valDemo['reference_table']);

            Schema::dropIfExists($referenceTable);
            Schema::dropIfExists($referenceTable . '_call_logs');

            // ---
            // --- insert campaign info into 'campaigns' table
            // ---
            DB::table('campaigns')
                ->insert([
                    'unique_key' => Carbon::now('Asia/Jakarta')->getTimestamp(),
                    'name' => $valDemo['name'],
                    'created_by' => 2,
                    'status' => $valDemo['status'],
                    'template_id' => $valDemo['template_id'],
                    'reference_table' => $referenceTable,
                    'text_voice' => $valDemo['text_voice'],
                    'voice_gender' => $valDemo['voice_gender'],
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s')
                ]);

            // ---
            // --- create reference contacts table
            // ---
            Schema::create($referenceTable, function (Blueprint $table) {
                // $table->engine = 'MyISAM';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->increments('id');
                $table->string('product_name', 30);
                $table->string('phone', 15);
                $table->date('due_date');
                $table->integer('nominal')->unsigned();
                $table->integer('extension')->unsigned()->nullable();
                $table->string('callerid')->nullable();
                $table->text('voice')->nullable();
                $table->tinyInteger('total_calls')->unsigned()->nullable();
                $table->datetime('call_dial')->index()->nullable();
                $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                $table->timestamps();
                $table->softDeletes();
            });

            // ---
            // --- create reference contact logs table
            // ---
            Schema::create($referenceTable . '_call_logs', function (Blueprint $table) {
                // $table->engine = 'MyISAM';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                
                $table->increments('id');
                $table->integer('contact_id')->unsigned();
                $table->datetime('call_dial')->nullable();
                $table->datetime('call_connect')->nullable();
                $table->datetime('call_disconnect')->nullable();
                $table->integer('call_duration')->default(0)->nullable();
                $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
                $table->string('call_recording', 255)->nullable();
                $table->timestamps();
            });

            sleep(1); // delay to make new timestamp
        }
    }
}
