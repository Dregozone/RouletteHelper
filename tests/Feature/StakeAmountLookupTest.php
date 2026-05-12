<?php

// Feature 7: Stake Amount Lookup (SuperSafe Strategy)

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('the component uses the SuperSafe betting strategy by default', function () {
    expect(Livewire::test('pages::main.page')->get('behaviour'))->toBe('SuperSafe');
});

test('the stakes array is populated from the SuperSafe strategy on mount', function () {
    $stakes = Livewire::test('pages::main.page')->get('stakes');

    expect($stakes[0])->toBe(0); // index 0
    expect($stakes[1])->toBe(0); // index 1
    expect($stakes[2])->toBe(1); // index 2
    expect($stakes[3])->toBe(1); // index 3
    expect($stakes[4])->toBe(2); // index 4
});

test('a streak of 2 shows the SuperSafe stake for index 1 which is 0', function () {
    // 19, 21 both high — high streak = 2 → Low recommendation shows stakes[1] = 0
    insertRollsOrdered([19, 21]);

    Livewire::test('pages::main.page')->assertSee('£0');
});

test('a streak of 3 shows the SuperSafe stake for index 2 which is 1', function () {
    // 19, 21, 23 all high — high streak = 3 → stakes[2] = 1
    insertRollsOrdered([19, 21, 23]);

    Livewire::test('pages::main.page')->assertSee('£1');
});

test('a streak of 5 shows the SuperSafe stake for index 4 which is 2', function () {
    // 5 consecutive high rolls — high streak = 5 → stakes[4] = 2
    $records = [];
    $highs = [19, 21, 23, 25, 27];
    foreach ($highs as $i => $num) {
        $timestamp = now()->subSeconds(count($highs) - 1 - $i)->toDateTimeString();
        $records[] = ['num' => $num, 'created_at' => $timestamp, 'updated_at' => $timestamp];
    }
    Historical::insert($records);

    Livewire::test('pages::main.page')->assertSee('£2');
});

test('a streak beyond the stake array explains that the stake ladder has no configured value', function () {
    // SuperSafe index 5 = null → the recommendation stays live but cannot display a configured stake.
    $records = [];
    for ($i = 0; $i < 6; $i++) {
        $timestamp = now()->subSeconds(6 - $i)->toDateTimeString();
        $records[] = ['num' => 19, 'created_at' => $timestamp, 'updated_at' => $timestamp];
    }
    Historical::insert($records);

    Livewire::test('pages::main.page')
        ->assertSee('Stake not set for this step.');
});
