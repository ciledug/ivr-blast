<?php

use Illuminate\Database\Seeder;

class ColumnTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('column_types')->truncate();

        DB::table('column_types')->insert(['name' => 'Text', 'type' => 'string']);
        DB::table('column_types')->insert(['name' => 'Date Time', 'type' => 'datetime']);
        DB::table('column_types')->insert(['name' => 'Date', 'type' => 'date']);
        DB::table('column_types')->insert(['name' => 'Time', 'type' => 'time']);
        DB::table('column_types')->insert(['name' => 'Numeric', 'type' => 'numeric']);
        DB::table('column_types')->insert(['name' => 'Handphone', 'type' => 'handphone']);
    }
}
