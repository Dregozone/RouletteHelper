<?php

// Feature 5: Streak Calculation Engine

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('a single even roll starts an even streak of 1', function () {
    insertRollsOrdered([2]); // 2 is even, low, black

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(1);
    expect($counts['odd'])->toBe(0);
});

test('a single odd roll starts an odd streak of 1', function () {
    insertRollsOrdered([1]); // 1 is odd, low, red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['odd'])->toBe(1);
    expect($counts['even'])->toBe(0);
});

test('consecutive even rolls produce the correct even streak count', function () {
    insertRollsOrdered([2, 4, 6]); // 6 is most recent

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(3);
    expect($counts['odd'])->toBe(0);
});

test('consecutive odd rolls produce the correct odd streak count', function () {
    insertRollsOrdered([1, 3, 5]); // 5 is most recent; all odd, low, red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['odd'])->toBe(3);
    expect($counts['even'])->toBe(0);
});

test('consecutive high rolls produce the correct high streak count', function () {
    insertRollsOrdered([19, 21, 23]); // all odd, high, red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['high'])->toBe(3);
    expect($counts['low'])->toBe(0);
});

test('consecutive low rolls produce the correct low streak count', function () {
    insertRollsOrdered([1, 3, 5]); // all odd, low, red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['low'])->toBe(3);
    expect($counts['high'])->toBe(0);
});

test('consecutive red rolls produce the correct red streak count', function () {
    insertRollsOrdered([1, 3, 5]); // all odd, low, red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['red'])->toBe(3);
    expect($counts['black'])->toBe(0);
});

test('consecutive black rolls produce the correct black streak count', function () {
    insertRollsOrdered([2, 4, 6]); // all even, low, black

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['black'])->toBe(3);
    expect($counts['red'])->toBe(0);
});

test('rolling zero as the most recent roll sets all streak counts to zero', function () {
    insertRollsOrdered([2, 4, 0]); // 0 is most recent

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(0);
    expect($counts['odd'])->toBe(0);
    expect($counts['low'])->toBe(0);
    expect($counts['high'])->toBe(0);
    expect($counts['red'])->toBe(0);
    expect($counts['black'])->toBe(0);
});

test('a zero in the middle of rolls breaks the streak', function () {
    // Sequence oldest→newest: 2, 4, 0, 6
    // Component sees (desc): 6, 0, 4, 2
    // 6 starts even streak; 0 breaks it; 4 and 2 cannot continue the broken streak
    insertRollsOrdered([2, 4, 0, 6]);

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(1); // only 6 before the streak was broken by 0
});

test('an opposite roll breaks the even streak', function () {
    insertRollsOrdered([2, 4, 1]); // 1 is odd — most recent, breaks even streak

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(0);
    expect($counts['odd'])->toBe(1);
});

test('an opposite roll breaks the high streak', function () {
    insertRollsOrdered([19, 21, 2]); // 2 is low — most recent, breaks high streak

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['high'])->toBe(0);
    expect($counts['low'])->toBe(1);
});

test('an opposite roll breaks the red streak', function () {
    insertRollsOrdered([1, 3, 2]); // 2 is black — most recent, breaks red streak

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['red'])->toBe(0);
    expect($counts['black'])->toBe(1);
});

test('multiple streaks can be active simultaneously', function () {
    insertRollsOrdered([20, 22, 24]); // all even, high, black

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(3);
    expect($counts['high'])->toBe(3);
    expect($counts['black'])->toBe(3);
    expect($counts['odd'])->toBe(0);
    expect($counts['low'])->toBe(0);
    expect($counts['red'])->toBe(0);
});

test('streak calculations are capped at the last 15 rolls', function () {
    $records = [];
    for ($i = 0; $i < 20; $i++) {
        $timestamp = now()->subSeconds(20 - $i)->toDateTimeString();
        $records[] = ['num' => 2, 'created_at' => $timestamp, 'updated_at' => $timestamp];
    }
    Historical::insert($records);

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBe(15); // capped at 15 records
});

test('even and odd streaks are mutually exclusive', function () {
    insertRollsOrdered([2, 4, 6]); // all even

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['even'])->toBeGreaterThan(0);
    expect($counts['odd'])->toBe(0);
});

test('high and low streaks are mutually exclusive', function () {
    insertRollsOrdered([19, 21, 23]); // all high

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['high'])->toBeGreaterThan(0);
    expect($counts['low'])->toBe(0);
});

test('red and black streaks are mutually exclusive', function () {
    insertRollsOrdered([1, 3, 5]); // all red

    $counts = Livewire::test('pages::main.page')->get('counts');

    expect($counts['red'])->toBeGreaterThan(0);
    expect($counts['black'])->toBe(0);
});
