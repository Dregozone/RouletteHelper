<?php

// Feature 4: Historical Rolls Table Display

use App\Models\Historical;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('only the last 15 rolls are displayed when more than 15 exist', function () {
    $records = [];
    for ($i = 1; $i <= 20; $i++) {
        $timestamp = now()->subSeconds(20 - $i)->toDateTimeString();
        $records[] = ['num' => $i % 37, 'created_at' => $timestamp, 'updated_at' => $timestamp];
    }
    Historical::insert($records);

    expect(Livewire::test('pages::main.page')->get('historicals'))->toHaveCount(15);
});

test('rolls are displayed most recent first', function () {
    insertRollsOrdered([5, 12, 20]); // 20 is most recent

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['num'])->toBe(20);
    expect($historicals[1]['num'])->toBe(12);
    expect($historicals[2]['num'])->toBe(5);
});

test('number zero has all property fields set to falsy', function () {
    insertRollsOrdered([0]);

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['num'])->toBe(0);
    expect($historicals[0]['isEven'])->toBeFalsy();
    expect($historicals[0]['isOdd'])->toBeFalsy();
    expect($historicals[0]['isLow'])->toBeFalsy();
    expect($historicals[0]['isHigh'])->toBeFalsy();
    expect($historicals[0]['isRed'])->toBeFalsy();
    expect($historicals[0]['isBlack'])->toBeFalsy();
});

test('a red number has isRed true and isBlack false', function () {
    insertRollsOrdered([1]); // 1 is odd, low, red

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isRed'])->toBeTruthy();
    expect($historicals[0]['isBlack'])->toBeFalsy();
});

test('a black number has isBlack true and isRed false', function () {
    insertRollsOrdered([2]); // 2 is even, low, black

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isBlack'])->toBeTruthy();
    expect($historicals[0]['isRed'])->toBeFalsy();
});

test('an even number has isEven true and isOdd false', function () {
    insertRollsOrdered([2]); // 2 is even

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isEven'])->toBeTruthy();
    expect($historicals[0]['isOdd'])->toBeFalsy();
});

test('an odd number has isOdd true and isEven false', function () {
    insertRollsOrdered([1]); // 1 is odd

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isOdd'])->toBeTruthy();
    expect($historicals[0]['isEven'])->toBeFalsy();
});

test('a high number (19-36) has isHigh true and isLow false', function () {
    insertRollsOrdered([19]); // 19 is odd, high, red

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isHigh'])->toBeTruthy();
    expect($historicals[0]['isLow'])->toBeFalsy();
});

test('a low number (1-18) has isLow true and isHigh false', function () {
    insertRollsOrdered([1]); // 1 is odd, low, red

    $historicals = Livewire::test('pages::main.page')->get('historicals');

    expect($historicals[0]['isLow'])->toBeTruthy();
    expect($historicals[0]['isHigh'])->toBeFalsy();
});
