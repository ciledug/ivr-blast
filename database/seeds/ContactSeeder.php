<?php

use Illuminate\Database\Seeder;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Faker\Factory as Faker;

use App\Campaign;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    /*
    public function run()
    {
        DB::table('contacts')->truncate();

        $referenceTable = 'c_demo_t_demo_0000001';
        $faker = Faker::create('id_ID');
        $lastWeek = Carbon::now('Asia/Jakarta')->addDays(7);

        // according to CampaignSeeder
        $campaignIds = [ 1, ];
        $campaignContactsCount = [ 100, ];
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
        $tempNominal = 0;

        foreach ($campaignContactsCount AS $keyCampaignContact => $valueContactCount) {
            for ($i = 1; $i <= $valueContactCount; $i++) {
                $operatorIdx = rand(0, count($operators) - 1);
                $tempNominal = $faker->numberBetween(100000, 1500000);

                if (($i % 10) == 0) {
                    $lastWeek = Carbon::now('Asia/Jakarta')->addDays(7 + ($i / 10));
                }

                DB::table($referenceTable)->insert([
                    'product_name' => 'Produk Demo IVR Blast',
                    'phone' => $operators[$operatorIdx] . $faker->numberBetween(100000, 99999999),
                    'due_date' => $lastWeek->format('Y-m-d'),
                    'nominal' => $tempNominal,
                    'extension' => null,
                    'callerid' => null,
                    'voice' => Helpers::generateVoice(
                        array(
                            (object) array('name' => 'product_name', 'column_type' => 'string'),
                            (object) array('name' => 'nominal', 'column_type' => 'numeric'),
                            (object) array('name' => 'due_date', 'column_type' => 'date'),
                        ),
                        (object) array(
                            'product_name' => 'Demo IVR Blast',
                            'nominal' => $tempNominal,
                            'due_date' => $lastWeek->format('Y-m-d'),
                        )
                    ),
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);
            }

            DB::table('campaigns')
                ->where('id', '=', $campaignIds[$keyCampaignContact])
                ->update([ 'total_data' => $valueContactCount ]);
        }
    }
    */

    public function run()
    {
        DB::table('contacts')->truncate();
        
        $campaigns = Campaign::whereNotIn('id', [1])->get();
        $faker = Faker::create('id_ID');
        $lastWeek = Carbon::now('Asia/Jakarta')->subDays(7);
        $contactsCount = 100;

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
        $tempNominal = 0;

        foreach ($campaigns AS $keyRefTable => $valRefTable) {
            for ($i = 1; $i <= $contactsCount; $i++) {
                $operatorIdx = rand(0, count($operators) - 1);
                $tempNominal = $faker->numberBetween(100000, 1500000);

                if (($i % 10) == 0) {
                    $lastWeek = Carbon::now('Asia/Jakarta')->subDays(7 + ($i / 10));
                }

                DB::table(strtolower($valRefTable['reference_table']))->insert([
                    'product_name' => $valRefTable['name'],
                    'phone' => $operators[$operatorIdx] . $faker->numberBetween(100000, 99999999),
                    'due_date' => $lastWeek->format('Y-m-d'),
                    'nominal' => $tempNominal,
                    'extension' => null,
                    'callerid' => null,
                    'voice' => Helpers::generateVoice(
                        $valRefTable['text_voice'],
                        array(
                            (object) array('name' => 'product_name', 'column_type' => 'string'),
                            (object) array('name' => 'nominal', 'column_type' => 'numeric'),
                            (object) array('name' => 'due_date', 'column_type' => 'date'),
                        ),
                        (object) array(
                            'product_name' => $valRefTable['name'],
                            'nominal' => $tempNominal,
                            'due_date' => $lastWeek->format('Y-m-d'),)
                    ),
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);
            }

            DB::table('campaigns')
                ->where('id', '=', $valRefTable['id'])
                ->update([ 'total_data' => $contactsCount ]);
        }
    }
}
