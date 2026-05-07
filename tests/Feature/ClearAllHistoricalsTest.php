<?php

// Feature 3: Clear All Historical Rolls

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('clearing all historicals removes every record from the database', function () {
    insertRollsOrdered([5, 10, 15]);

    expect(Historical::count())->toBe(3);

    Livewire::test('pages::main.page')
        ->call('doAction', 'clearAllHistorical');

    expect(Historical::count())->toBe(0);
});

test('clearing all historicals empties the historicals list on the component', function () {
    insertRollsOrdered([5]);

    $component = Livewire::test('pages::main.page')
        ->call('doAction', 'clearAllHistorical');

    expect($component->get('historicals'))->toBeEmpty();
});

test('clearing all historicals resets all six streak counts to zero', function () {
    // 2, 4, 6 are all even — creates a streak before clearing
    insertRollsOrdered([2, 4, 6]);

    $component = Livewire::test('pages::main.page');
    expect($component->get('counts')['even'])->toBe(3);

    $component->call('doAction', 'clearAllHistorical');

    $counts = $component->get('counts');
    expect($counts['even'])->toBe(0);
    expect($counts['odd'])->toBe(0);
    expect($counts['low'])->toBe(0);
    expect($counts['high'])->toBe(0);
    expect($counts['red'])->toBe(0);
    expect($counts['black'])->toBe(0);
});
