<?php

use App\Models\Historical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Shared Test Helpers
|--------------------------------------------------------------------------
*/

/**
 * Insert Historical records with deterministic created_at timestamps so that
 * the first element of $nums becomes the oldest record and the last becomes
 * the most recent.  The component orders results DESC by created_at, so after
 * insertion the component will display the last element first.
 *
 * @param  int[]  $nums  Numbers in chronological order (oldest → newest)
 */
function insertRollsOrdered(array $nums): void
{
    $count = count($nums);
    $records = [];

    foreach ($nums as $index => $num) {
        // Start at $count seconds ago so the last element is always at least 1 second
        // in the past, guaranteeing any subsequent Historical::create() call is newer.
        $secondsAgo = $count - $index;
        $timestamp = now()->subSeconds($secondsAgo)->toDateTimeString();
        $records[] = [
            'num' => $num,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    Historical::insert($records);
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
