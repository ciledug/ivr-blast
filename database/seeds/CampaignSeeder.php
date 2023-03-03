<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        DB::table('campaigns')->truncate();

        $campaignNames = [
            'First Campaign',
            'Second Campaign',
            'Third Campaign',
            'Fourth Campaign',
            'Fifth Campaign'
        ];

        foreach ($campaignNames AS $keyCampaignName => $valueCampaignValue) {
            $today = Carbon::now('Asia/Jakarta');
            DB::table('campaigns')
                ->insert([
                    'unique_key' => $today->getTimestamp(),
                    'name' => $valueCampaignValue,
                    'created_by' => 1,
                    'created_at' => $today->format('Y-m-d H:i:s'),
                    'updated_at' => $today->format('Y-m-d H:i:s')
                ]);
            sleep(1);
        }
    }
}
