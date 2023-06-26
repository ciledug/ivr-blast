<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'username' => 'sadmin',
            'email' => 'sadmin@ivrblast.com',
            'password' => Hash::make('123456'),
            'added_by' => 'sadmin',
        ]);

        DB::table('users')->insert([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@ivrblast.com',
            'password' => Hash::make('123456'),
            'added_by' => 'sadmin',
        ]);
    }
}
