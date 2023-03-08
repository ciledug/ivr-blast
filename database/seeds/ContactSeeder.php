<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contacts')->truncate();

        $faker = Faker::create('id_ID');
        $nextWeek = Carbon::now('Asia/Jakarta')->addDays(7);

        // according to CampaignSeeder
        $campaignNames = [
            'First Campaign',
            'Second Campaign',
            'Third Campaign',
            'Fourth Campaign',
            'Fifth Campaign'
        ];
        $campaignContactsCount = [
            100, 1000, 5000, 10000, 300000
        ];
        // according to CampaignSeeder

        $operators = [
            '0811', '0812', '0813',
            '0817', '0818', '0819',
            '0821', '0822',
            '0851', '0852', '0853',
            '0856', '0857', '0858',
            '0876', '0877', '0878',
            '0881', '0882', '0883',
            '0895', '0896', '0897'
        ];
        $operatorIdx = 0;

        foreach ($campaignContactsCount AS $keyCampaignContact => $valueContactCount) {
            for ($i = 1; $i <= $valueContactCount; $i++) {
                $operatorIdx = rand(0, count($operators) - 1);
    
                DB::table('contacts')->insert([
                    'campaign_id' => $keyCampaignContact + 1,
                    'account_id' => '0' . ($keyCampaignContact + 1) . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'name' => $faker->name,
                    'phone' => $operators[$operatorIdx] . $faker->numberBetween(100000, 99999999),
                    'bill_date' => $nextWeek->format('Y-m-d'),
                    'due_date' => $nextWeek->format('Y-m-d'),
                    'nominal' => $faker->numberBetween(100000, 99999999)
                ]);
            }

            DB::table('campaigns')
                ->where('name', '=', $campaignNames[$keyCampaignContact])
                ->update([ 'total_data' => $valueContactCount ]);
        }
    }
}
