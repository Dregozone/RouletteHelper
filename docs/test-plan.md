# RouletteHelper — Test Plan

This document describes every test in the suite, what it checks, and why it exists. All feature references point to items in [docs/feature-list.md](feature-list.md).

Running the full suite: `php artisan test --compact`

---

## Shared Infrastructure

### `tests/Pest.php` — `insertRollsOrdered(array $nums)`

A shared helper used by most tests. It inserts `Historical` records with deterministic `created_at` timestamps so that the last element of `$nums` is always the most-recently-inserted record. This guarantees that the component's `DESC created_at` ordering is predictable across all tests that need a specific sequence of rolls in history.

---

## Feature 1 — Record a Roll

**File:** `tests/Feature/RecordARollTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| recording a roll creates a new historical record in the database | Calling `doAction('recordARoll')` with a number inserts exactly one row into `historicals` with the correct `num` value | Core persistence — without this the entire app has no data |
| recorded roll appears in the historicals list on the component | After saving, the component's `$historicals` property is reloaded and contains the new record | Confirms the reactive data refresh works after a save |
| can record the minimum number zero | Number `0` (green pocket) can be saved successfully | `0` is a valid roulette outcome and must be storable |
| can record the maximum number 36 | Number `36` can be saved successfully | Boundary check — ensures the upper limit of the roulette wheel is accepted |
| the same number can be recorded multiple times | Saving the same number three times produces three separate records | Roulette wheels can land on the same number repeatedly; duplicates are intentional |
| multiple different rolls can be recorded consecutively | Saving five different numbers in sequence all persist | Smoke test for the normal usage pattern across a session |

---

## Feature 2 — Delete Individual Roll

**File:** `tests/Feature/DeleteARollTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| deleting a roll removes it from the database | Calling `doAction('deleteAHistorical', $id)` removes the matching row | Core persistence — the record must actually be deleted |
| deleted roll disappears from the historicals list | After deletion the component's `$historicals` no longer contains the removed record | Confirms the reactive data refresh works after a delete |
| deleting one roll leaves the other records intact | With three records present, deleting one leaves exactly two | Ensures a targeted delete does not accidentally wipe other rows |
| streak counts are recalculated after a roll is deleted | An even streak of 3 drops to 2 when the most-recent roll is deleted | Streak state must stay consistent with the actual history; stale counts would produce wrong recommendations |

---

## Feature 3 — Clear All Historical Rolls

**File:** `tests/Feature/ClearAllHistoricalsTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| clearing all historicals removes every record from the database | `doAction('clearAllHistorical')` truncates the table entirely | The Clear All action must have complete effect on the database |
| clearing all historicals empties the historicals list on the component | After clearing, the component's `$historicals` is empty | Confirms the view reacts correctly to an empty table |
| clearing all historicals resets all six streak counts to zero | All six entries in `$counts` become 0 after clearing | With no history there can be no streak; counts left at non-zero values would show phantom recommendations |

---

## Feature 4 — Historical Rolls Table Display

**File:** `tests/Feature/HistoricalDisplayTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| only the last 15 rolls are displayed when more than 15 exist | Inserting 20 records still results in `$historicals` containing exactly 15 items | The display is intentionally capped; showing more would clutter the UI and distort the streak window |
| rolls are displayed most recent first | Given rolls `[5, 12, 20]` the first item in `$historicals` is `20` | Chronological order affects how the user reads the history table |
| number zero has all property fields set to falsy | A recorded `0` has `isEven`, `isOdd`, `isLow`, `isHigh`, `isRed`, `isBlack` all false | Zero is the green pocket — it belongs to no category and the display must show it as neutral |
| a red number has isRed true and isBlack false | Recording `1` (a known red number) yields `isRed = true`, `isBlack = false` | Correct colour-coding in the table depends on the join with `possible_outcomes` returning the right flags |
| a black number has isBlack true and isRed false | Recording `2` (a known black number) yields `isBlack = true`, `isRed = false` | Same as above for the black case |
| an even number has isEven true and isOdd false | Recording `2` yields `isEven = true`, `isOdd = false` | Ensures the even/odd indicator column displays correctly |
| an odd number has isOdd true and isEven false | Recording `1` yields `isOdd = true`, `isEven = false` | Ensures the odd indicator displays correctly |
| a high number (19-36) has isHigh true and isLow false | Recording `19` yields `isHigh = true`, `isLow = false` | Ensures the high/low indicator column displays correctly for the lower boundary of the high range |
| a low number (1-18) has isLow true and isHigh false | Recording `1` yields `isLow = true`, `isHigh = false` | Ensures the low indicator displays correctly |

---

## Feature 5 — Streak Calculation Engine

**File:** `tests/Feature/StreakCalculationTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| a single even roll starts an even streak of 1 | One even roll produces `counts['even'] = 1`, `counts['odd'] = 0` | Baseline: the very first roll must initialise the correct streak |
| a single odd roll starts an odd streak of 1 | One odd roll produces `counts['odd'] = 1`, `counts['even'] = 0` | Baseline for the odd type |
| consecutive even rolls produce the correct even streak count | Three consecutive even rolls produce `counts['even'] = 3` | Verifies the streak increments correctly for each matching roll |
| consecutive odd rolls produce the correct odd streak count | Three consecutive odd rolls produce `counts['odd'] = 3` | Same for the odd type |
| consecutive high rolls produce the correct high streak count | Three consecutive high rolls produce `counts['high'] = 3` | Same for the high type |
| consecutive low rolls produce the correct low streak count | Three consecutive low rolls produce `counts['low'] = 3` | Same for the low type |
| consecutive red rolls produce the correct red streak count | Three consecutive red rolls produce `counts['red'] = 3` | Same for the red type |
| consecutive black rolls produce the correct black streak count | Three consecutive black rolls produce `counts['black'] = 3` | Same for the black type |
| rolling zero as the most recent roll sets all streak counts to zero | With `[2, 4, 0]` where `0` is most recent, all six counts are 0 | Zero must immediately terminate every streak — it is the most common streak-breaking case |
| a zero in the middle of rolls breaks the streak | Sequence `[2, 4, 0, 6]` produces `counts['even'] = 1` (only `6` survives) | Zero must break the streak even when it appears historically between other rolls |
| an opposite roll breaks the even streak | Sequence `[2, 4, 1]` (odd most recent) produces `counts['even'] = 0`, `counts['odd'] = 1` | Mutually exclusive categories must terminate each other's streak |
| an opposite roll breaks the high streak | Sequence `[19, 21, 2]` (low most recent) produces `counts['high'] = 0` | Same for the high/low pair |
| an opposite roll breaks the red streak | Sequence `[1, 3, 2]` (black most recent) produces `counts['red'] = 0` | Same for the red/black pair |
| multiple streaks can be active simultaneously | Three even+high+black rolls produce counts of 3 for all three types | A single roll can belong to multiple categories; all matching streaks must increment together |
| streak calculations are capped at the last 15 rolls | 20 identical even rolls produce `counts['even'] = 15`, not 20 | The component only examines the 15 most recent records; this is both a business rule and a performance guard |
| even and odd streaks are mutually exclusive | An even streak is > 0 while the odd count is exactly 0 | Structural invariant — these two categories cannot both have a count at the same time |
| high and low streaks are mutually exclusive | A high streak is > 0 while the low count is exactly 0 | Same invariant for the high/low pair |
| red and black streaks are mutually exclusive | A red streak is > 0 while the black count is exactly 0 | Same invariant for the red/black pair |

---

## Feature 6 — Betting Recommendations Display

**File:** `tests/Feature/BettingRecommendationsTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| no stake amount is shown when all streaks are below 2 | A single roll results in no `£` symbol rendered anywhere | With a streak of only 1 there is no actionable signal; the UI must stay quiet |
| low recommendation is shown when the high streak reaches 2 | Two consecutive high rolls render a `£` in the page HTML | The threshold for triggering a recommendation is 2; confirms it activates at exactly that point |
| high recommendation is shown when the low streak reaches 2 | Two consecutive low rolls render a `£` | Same threshold check for the high recommendation |
| even recommendation is shown when the odd streak reaches 2 | Two consecutive odd rolls render `Even` and `£` in the page | Confirms the reverse-logic label (bet Even because Odd is on a streak) appears |
| odd recommendation is shown when the even streak reaches 2 | Two consecutive even rolls render `Odd` and `£` | Same reverse-logic check for the Odd recommendation |
| red recommendation is shown when the black streak reaches 2 | Two consecutive black rolls render `Red` and `£` | Same for the Red recommendation |
| black recommendation is shown when the red streak reaches 2 | Two consecutive red rolls render `Black` and `£` | Same for the Black recommendation |
| recommendation disappears when its triggering streak drops below 2 | After a high streak of 2 is broken by recording a low roll, `counts['high']` returns to 0 | A recommendation that no longer applies must not persist; stale recommendations could cause incorrect bets |

---

## Feature 7 — Stake Amount Lookup (SuperSafe Strategy)

**File:** `tests/Feature/StakeAmountLookupTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| the component uses the SuperSafe betting strategy by default | `$behaviour` is `'SuperSafe'` on mount | Documents and protects the hardcoded default so a future change is noticed |
| the stakes array is populated from the SuperSafe strategy on mount | `$stakes[0..4]` equal `[0, 0, 1, 1, 2]` respectively | The array must match the seeded SuperSafe values; any mismatch means wrong bet amounts are shown |
| a streak of 2 shows the SuperSafe stake for index 1 which is 0 | The rendered page contains `£0` when the high streak is 2 | `stakes[streak - 1]` = `stakes[1]` = `0`; verifies the index arithmetic is correct |
| a streak of 3 shows the SuperSafe stake for index 2 which is 1 | The rendered page contains `£1` when the high streak is 3 | `stakes[2]` = `1`; step-up in stake amount |
| a streak of 5 shows the SuperSafe stake for index 4 which is 2 | The rendered page contains `£2` when the high streak is 5 | `stakes[4]` = `2`; tests the largest non-null SuperSafe value |
| a streak beyond the stake array shows x for null values | A high streak of 6 renders `£x` | `stakes[5]` = `null`; the template uses `?? 'x'` to handle this gracefully — must not show a blank or crash |

---

## Feature 8 — PossibleOutcome Reference Data (Seed Integrity)

**File:** `tests/Feature/PossibleOutcomeDataTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| all 37 roulette numbers are seeded in possible outcomes | `PossibleOutcome::count()` is 37 | Every number on a European wheel must be present; a missing entry would break the JOIN used for every roll |
| every number from 0 to 36 is present exactly once | Sorted `num` values exactly equal `range(0, 36)` | Rules out both duplicates and gaps in the seed data |
| number zero has all six properties set to false | Zero's six boolean fields are all falsy | Zero is the green pocket — no category applies, and incorrect flags would corrupt streaks and display |
| red numbers match the official european roulette layout | The 18 red numbers match exactly `[1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36]` | These are fixed by the physical wheel; any difference means recommendations based on red/black would be wrong |
| numbers 19 through 36 are classified as high | `isHigh = true` for exactly `range(19, 36)` | The high/low boundary is a game rule; wrong classification corrupts all high/low streaks |
| numbers 1 through 18 are classified as low | `isLow = true` for exactly `range(1, 18)` | Same boundary check from the low side |
| even numbers 2 through 36 are correctly classified | `isEven = true` for all even integers 2–36 | Mathematical parity must match the wheel's actual even numbers |
| odd numbers 1 through 35 are correctly classified | `isOdd = true` for all odd integers 1–35 | Same for odd numbers |
| every non-zero number is either red or black but not both | XOR of `isRed` and `isBlack` is true for all `num != 0` | Structural integrity — a number belonging to both or neither would corrupt colour streaks |
| every non-zero number is either even or odd but not both | XOR of `isEven` and `isOdd` is true for all `num != 0` | Same integrity check for the even/odd pair |
| every non-zero number is either high or low but not both | XOR of `isHigh` and `isLow` is true for all `num != 0` | Same integrity check for the high/low pair |

---

## Feature 9 — Stake Reference Data (Seed Integrity)

**File:** `tests/Feature/StakeDataTest.php`

| Test | What it checks | Why it exists |
|---|---|---|
| exactly three stake strategies are seeded | `Stake::count()` is 3 | Prevents accidental duplicate seeds or missing strategies |
| the plain strategy exists with correct stake values | `Plain` stakes decode to `[0,1,2,4,8,16,32,64]` | Documents the intended Plain values and catches any seed regression |
| the safe strategy exists with correct stake values | `Safe` stakes decode to `[0,1,1,2,4,null,null,null]` | Documents the intended Safe values |
| the supersafe strategy exists with correct stake values | `SuperSafe` stakes decode to `[0,0,1,1,2,null,null,null]` | Directly protects the active strategy used by the component; a wrong value would show incorrect bet amounts |
| stake strategy names are unique | No two strategies share a `name` | The component looks up a strategy by name; duplicates would return an unpredictable record |
| each stake strategy has a valid json stakes field | `json_last_error()` is `JSON_ERROR_NONE` for all three | The component calls `json_decode` on this field; invalid JSON would silently produce `null` and break stake display |
