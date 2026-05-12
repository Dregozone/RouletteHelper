<?php

// Feature 6: Betting Recommendations Display

use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('no recommendations are live when all streaks are below 2', function () {
    insertRollsOrdered([5]); // single roll, no streak reaches 2

    Livewire::test('pages::main.page')
        ->assertSee('No bet live');
});

test('low recommendation is shown when the high streak reaches 2', function () {
    insertRollsOrdered([19, 21]); // both odd, high, red — high streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['high'])->toBe(2);
    $component->assertSee('£');
});

test('high recommendation is shown when the low streak reaches 2', function () {
    insertRollsOrdered([1, 3]); // both odd, low, red — low streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['low'])->toBe(2);
    $component->assertSee('£');
});

test('even recommendation is shown when the odd streak reaches 2', function () {
    insertRollsOrdered([1, 3]); // both odd — odd streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['odd'])->toBe(2);
    $component->assertSee('Even');
    $component->assertSee('£');
});

test('odd recommendation is shown when the even streak reaches 2', function () {
    insertRollsOrdered([2, 4]); // both even, low, black — even streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['even'])->toBe(2);
    $component->assertSee('Odd');
    $component->assertSee('£');
});

test('red recommendation is shown when the black streak reaches 2', function () {
    insertRollsOrdered([2, 4]); // both black — black streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['black'])->toBe(2);
    $component->assertSee('Red');
    $component->assertSee('£');
});

test('black recommendation is shown when the red streak reaches 2', function () {
    insertRollsOrdered([1, 3]); // both red — red streak = 2

    $component = Livewire::test('pages::main.page');

    expect($component->get('counts')['red'])->toBe(2);
    $component->assertSee('Black');
    $component->assertSee('£');
});

test('recommendation disappears when its triggering streak drops below 2', function () {
    // Build a high streak of 2, then add a low roll to break it
    insertRollsOrdered([19, 21]); // high streak = 2 — triggers Low recommendation

    $component = Livewire::test('pages::main.page');
    expect($component->get('counts')['high'])->toBe(2);
    $component->assertSee('£'); // Low recommendation is active

    // Record a low roll to break the high streak
    $component->set('newRollNumber', 2)->call('doAction', 'recordARoll');

    expect($component->get('counts')['high'])->toBe(0); // streak broken
});
