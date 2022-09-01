<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PossibleOutcomesSeeder::class);
        $this->call(OppositesSeeder::class);
        $this->call(StakesSeeder::class);
    }
}
