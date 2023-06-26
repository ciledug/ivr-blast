<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TemplateHeaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('template_headers')->truncate();

        // ---
        // --- Create headers for default template
        // ---
        $headerList = [
            [
                array(
                    'template_id' => 1,
                    'name' => 'Account_ID',
                    'column_type' => 'string',
                    'is_mandatory' => true,
                    'is_unique' => true,
                    'is_voice' => false,
                    'voice_position' => null,
                ),
                array(
                    'template_id' => 1,
                    'name' => 'Name',
                    'column_type' => 'string',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => false,
                    'voice_position' => null,
                ),
                array(
                    'template_id' => 1,
                    'name' => 'Phone',
                    'column_type' => 'handphone',
                    'is_mandatory' => true,
                    'is_unique' => true,
                    'is_voice' => false,
                    'voice_position' => null,
                ),
                array(
                    'template_id' => 1,
                    'name' => 'Bill_Date',
                    'column_type' => 'date',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => false,
                    'voice_position' => null,
                ),
                array(
                    'template_id' => 1,
                    'name' => 'Due_Date',
                    'column_type' => 'date',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => true,
                    'voice_position' => 2,
                ),
                array(
                    'template_id' => 1,
                    'name' => 'Nominal',
                    'column_type' => 'numeric',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => true,
                    'voice_position' => 1,
                ),
            ],
            [
                array(
                    'template_id' => 2,
                    'name' => 'Product_Name',
                    'column_type' => 'string',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => true,
                    'voice_position' => 1,
                ),
                array(
                    'template_id' => 2,
                    'name' => 'Phone',
                    'column_type' => 'handphone',
                    'is_mandatory' => true,
                    'is_unique' => true,
                    'is_voice' => false,
                    'voice_position' => null,
                ),
                array(
                    'template_id' => 2,
                    'name' => 'Due_Date',
                    'column_type' => 'date',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => true,
                    'voice_position' => 3,
                ),
                array(
                    'template_id' => 2,
                    'name' => 'Nominal',
                    'column_type' => 'numeric',
                    'is_mandatory' => true,
                    'is_unique' => false,
                    'is_voice' => true,
                    'voice_position' => 2,
                ),
            ]
        ];
        
        foreach($headerList AS $keyHeader => $valueHeader) {
            foreach($valueHeader AS $key => $val) {
                DB::table('template_headers')->insert([
                    'template_id' => $val['template_id'],
                    'name' => $val['name'],
                    'column_type' => $val['column_type'],
                    'is_mandatory' => $val['is_mandatory'],
                    'is_unique' => $val['is_unique'],
                    'is_voice' => $val['is_voice'],
                    'voice_position' => $val['voice_position'],
                    'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
