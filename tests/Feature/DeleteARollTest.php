<?php

// Feature 2: Delete Individual Roll

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('deleting a roll removes it from the database', function () {
    insertRollsOrdered([7]);

    $id = Historical::first()->id;

    Livewire::test('pages::main.page')
        ->call('doAction', 'deleteAHistorical', $id);

    expect(Historical::count())->toBe(0);
});

test('deleted roll disappears from the historicals list', function () {
    insertRollsOrdered([7]);

    $id = Historical::first()->id;

    $component = Livewire::test('pages::main.page')
        ->call('doAction', 'deleteAHistorical', $id);

    expect($component->get('historicals'))->toBeEmpty();
});

test('deleting one roll leaves the other records intact', function () {
    insertRollsOrdered([5, 12, 20]);

    $idToDelete = Historical::orderBy('created_at', 'ASC')->first()->id;

    Livewire::test('pages::main.page')
        ->call('doAction', 'deleteAHistorical', $idToDelete);

    expect(Historical::count())->toBe(2);
});

test('streak counts are recalculated after a roll is deleted', function () {
    // 2, 4, 6 are all even — build an even streak of 3 (6 is most recent)
    insertRollsOrdered([2, 4, 6]);

    $newestId = Historical::orderBy('created_at', 'DESC')->first()->id;

    $component = Livewire::test('pages::main.page');
    expect($component->get('counts')['even'])->toBe(3);

    $component->call('doAction', 'deleteAHistorical', $newestId);

    // After deleting the most recent roll, streak drops to 2 (only 2 and 4 remain)
    expect($component->get('counts')['even'])->toBe(2);
});
