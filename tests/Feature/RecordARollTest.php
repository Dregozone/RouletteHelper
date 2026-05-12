<?php

// Feature 1: Record a Roll

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('recording a roll creates a new historical record in the database', function () {
    expect(Historical::count())->toBe(0);

    Livewire::test('pages::main.page')
        ->set('newRollNumber', 7)
        ->call('doAction', 'recordARoll');

    expect(Historical::count())->toBe(1);
    expect(Historical::first()->num)->toBe(7);
});

test('recorded roll appears in the historicals list on the component', function () {
    $component = Livewire::test('pages::main.page')
        ->set('newRollNumber', 7)
        ->call('doAction', 'recordARoll');

    $historicals = $component->get('historicals');

    expect($historicals)->toHaveCount(1);
    expect($historicals[0]['num'])->toBe(7);
});

test('can record the minimum number zero', function () {
    Livewire::test('pages::main.page')
        ->set('newRollNumber', 0)
        ->call('doAction', 'recordARoll');

    expect(Historical::count())->toBe(1);
    expect(Historical::first()->num)->toBe(0);
});

test('can record the maximum number 36', function () {
    Livewire::test('pages::main.page')
        ->set('newRollNumber', 36)
        ->call('doAction', 'recordARoll');

    expect(Historical::count())->toBe(1);
    expect(Historical::first()->num)->toBe(36);
});

test('the same number can be recorded multiple times', function () {
    $component = Livewire::test('pages::main.page');

    foreach ([7, 7, 7] as $num) {
        $component->set('newRollNumber', $num)->call('doAction', 'recordARoll');
    }

    expect(Historical::count())->toBe(3);
});

test('multiple different rolls can be recorded consecutively', function () {
    $component = Livewire::test('pages::main.page');

    foreach ([1, 5, 10, 22, 36] as $num) {
        $component->set('newRollNumber', $num)->call('doAction', 'recordARoll');
    }

    expect(Historical::count())->toBe(5);
});

test('out of range rolls are rejected', function () {
    Livewire::test('pages::main.page')
        ->set('newRollNumber', 37)
        ->call('doAction', 'recordARoll')
        ->assertHasErrors(['newRollNumber']);

    expect(Historical::count())->toBe(0);
});
