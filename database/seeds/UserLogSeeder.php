<?php

use Illuminate\Database\Seeder;

class UserLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_logs')
            ->insert([
                'user_id' => 1,
                'last_login' => '0000-00-00 00:00:00',
                'last_ip_address' => '0.0.0.0',
            ]);
    }
}
