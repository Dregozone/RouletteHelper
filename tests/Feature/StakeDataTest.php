<?php

// Feature 9: Stake Reference Data (Seed Integrity)

use App\Models\Stake;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

test('exactly three stake strategies are seeded', function () {
    expect(Stake::count())->toBe(3);
});

test('the plain strategy exists with correct stake values', function () {
    $plain = Stake::where('name', 'Plain')->first();

    expect($plain)->not->toBeNull();
    expect(json_decode($plain->stakes))->toBe([0, 1, 2, 4, 8, 16, 32, 64]);
});

test('the safe strategy exists with correct stake values', function () {
    $safe = Stake::where('name', 'Safe')->first();

    expect($safe)->not->toBeNull();
    expect(json_decode($safe->stakes))->toBe([0, 1, 1, 2, 4, null, null, null]);
});

test('the supersafe strategy exists with correct stake values', function () {
    $superSafe = Stake::where('name', 'SuperSafe')->first();

    expect($superSafe)->not->toBeNull();
    expect(json_decode($superSafe->stakes))->toBe([0, 0, 1, 1, 2, null, null, null]);
});

test('stake strategy names are unique', function () {
    $names = Stake::pluck('name')->toArray();

    expect(array_unique($names))->toHaveCount(count($names));
});

test('each stake strategy has a valid json stakes field', function () {
    Stake::all()->each(function ($stake) {
        json_decode($stake->stakes);

        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    });
});
