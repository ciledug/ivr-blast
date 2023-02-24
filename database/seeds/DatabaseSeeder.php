<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(UserLogSeeder::class);
        $this->call(CampaignSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(CallLogSeeder::class);
    }
}
