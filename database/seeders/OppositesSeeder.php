<?php

namespace Database\Seeders;

use App\Models\Opposite;
use Illuminate\Database\Seeder;

class OppositesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Opposite::truncate();

        Opposite::create([
            'type' => 'red',
            'opposite' => 'black',
        ]);

        Opposite::create([
            'type' => 'black',
            'opposite' => 'red',
        ]);

        Opposite::create([
            'type' => 'low',
            'opposite' => 'high',
        ]);

        Opposite::create([
            'type' => 'high',
            'opposite' => 'low',
        ]);

        Opposite::create([
            'type' => 'even',
            'opposite' => 'odd',
        ]);

        Opposite::create([
            'type' => 'odd',
            'opposite' => 'even',
        ]);
    }
}
