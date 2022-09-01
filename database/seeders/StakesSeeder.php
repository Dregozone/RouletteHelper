<?php

namespace Database\Seeders;

use App\Models\Stake;
use Illuminate\Database\Seeder;

class StakesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Stake::truncate();

        Stake::create([
            'name' => 'Plain',
            'stakes' => '[0,1,2,4,8,16,32,64]',
        ]);

        Stake::create([
            'name' => 'Safe',
            'stakes' => '[0,1,1,2,4,null,null,null]',
        ]);
    }
}
