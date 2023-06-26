<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('templates')->truncate();

        // ---
        // --- Create default template
        // ---
        DB::table('templates')->insert([
            'name' => 'Default Template',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);

        // ---
        // --- Create demo template
        // ---
        DB::table('templates')->insert([
            'name' => 'Demo Template',
            'created_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);
    }
}
