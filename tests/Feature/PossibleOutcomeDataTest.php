<?php

// Feature 8: PossibleOutcome Reference Data (Seed Integrity)

use App\Models\PossibleOutcome;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('all 37 roulette numbers are seeded in possible outcomes', function () {
    expect(PossibleOutcome::count())->toBe(37);
});

test('every number from 0 to 36 is present exactly once', function () {
    $nums = PossibleOutcome::pluck('num')->sort()->values()->toArray();

    expect($nums)->toBe(range(0, 36));
});

test('number zero has all six properties set to false', function () {
    $zero = PossibleOutcome::where('num', 0)->first();

    expect($zero->isEven)->toBeFalsy();
    expect($zero->isOdd)->toBeFalsy();
    expect($zero->isLow)->toBeFalsy();
    expect($zero->isHigh)->toBeFalsy();
    expect($zero->isRed)->toBeFalsy();
    expect($zero->isBlack)->toBeFalsy();
});

test('red numbers match the official european roulette layout', function () {
    $expectedRed = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

    $actualRed = PossibleOutcome::where('isRed', true)
        ->pluck('num')
        ->sort()
        ->values()
        ->toArray();

    expect($actualRed)->toBe($expectedRed);
});

test('numbers 19 through 36 are classified as high', function () {
    $highNumbers = PossibleOutcome::where('isHigh', true)
        ->pluck('num')
        ->sort()
        ->values()
        ->toArray();

    expect($highNumbers)->toBe(range(19, 36));
});

test('numbers 1 through 18 are classified as low', function () {
    $lowNumbers = PossibleOutcome::where('isLow', true)
        ->pluck('num')
        ->sort()
        ->values()
        ->toArray();

    expect($lowNumbers)->toBe(range(1, 18));
});

test('even numbers 2 through 36 are correctly classified', function () {
    $evenNumbers = PossibleOutcome::where('isEven', true)
        ->pluck('num')
        ->sort()
        ->values()
        ->toArray();

    $expectedEven = array_values(array_filter(range(2, 36), fn ($n) => $n % 2 === 0));

    expect($evenNumbers)->toBe($expectedEven);
});

test('odd numbers 1 through 35 are correctly classified', function () {
    $oddNumbers = PossibleOutcome::where('isOdd', true)
        ->pluck('num')
        ->sort()
        ->values()
        ->toArray();

    $expectedOdd = array_values(array_filter(range(1, 35), fn ($n) => $n % 2 !== 0));

    expect($oddNumbers)->toBe($expectedOdd);
});

test('every non-zero number is either red or black but not both', function () {
    PossibleOutcome::where('num', '!=', 0)->each(function ($outcome) {
        expect((bool) $outcome->isRed xor (bool) $outcome->isBlack)->toBeTrue();
    });
});

test('every non-zero number is either even or odd but not both', function () {
    PossibleOutcome::where('num', '!=', 0)->each(function ($outcome) {
        expect((bool) $outcome->isEven xor (bool) $outcome->isOdd)->toBeTrue();
    });
});

test('every non-zero number is either high or low but not both', function () {
    PossibleOutcome::where('num', '!=', 0)->each(function ($outcome) {
        expect((bool) $outcome->isHigh xor (bool) $outcome->isLow)->toBeTrue();
    });
});
