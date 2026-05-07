# RouletteHelper — Feature List

This document is the canonical reference for all application features and is used to ensure full test coverage.

---

## Application Context

Single-page Livewire app for tracking European roulette spins (0–36) and recommending counter-bets using a reverse-streak strategy. No authentication. One Livewire page component (`resources/views/pages/main/⚡page.blade.php`).

---

## Feature 1: Record a Roll

- User enters a number (0–36) and submits the form
- A new `Historical` record is saved to the database with that `num` value
- Duplicate numbers are allowed (same number can be recorded multiple times)
- After saving, `$historicals` and `$counts` are recalculated
- A success toast is shown on save

---

## Feature 2: Delete Individual Roll

- Each row in the historical table has a delete button
- Clicking delete removes that specific `Historical` record by ID
- After deletion, `$historicals` and `$counts` are recalculated
- A success toast is shown on delete
- Other historical records are not affected

---

## Feature 3: Clear All Historical Rolls

- A "Clear all" button truncates the entire `historicals` table
- After clearing, `$historicals` becomes empty
- All six `$counts` values reset to 0
- A success toast is shown

---

## Feature 4: Historical Rolls Table Display

- Shows at most the **last 15 rolls**, ordered most recent first
- Each row shows: number, Red/Black, Odd/Even, High/Low, and a delete button
- Number `0` displays all property columns as neutral (`-`) with light-grey text
- Red numbers display `Red` in red text
- Black numbers display `Black` in black text
- Even numbers display `Even` in orange text
- Odd numbers display `Odd` in purple text
- High numbers (19–36) display `High` in green text
- Low numbers (1–18) display `Low` in cyan text

---

## Feature 5: Streak Calculation Engine

- Calculates consecutive streaks for all 6 bet types: **even, odd, low, high, red, black**
- Only examines the last 15 historical records
- Iterates from most recent to oldest
- The **first** (most recent) roll initialises the streak flags and counts
- For each subsequent roll: if the streak flag is active and the roll matches, the count increments; otherwise the streak flag is set to false
- Rolling a `0` **immediately breaks all six streaks** and sets all flags to false
- Each pair is mutually exclusive (even/odd, high/low, red/black) — an opposite-category roll breaks the streak
- The final `$counts` array contains the length of the most recent consecutive run per type

---

## Feature 6: Betting Recommendations Display

Six recommendation boxes are always visible. Each activates when the **opposite** bet type has a streak of 2 or more:

| Box label | Activates when         |
|-----------|------------------------|
| Low       | `$counts["high"] >= 2` |
| Even      | `$counts["odd"] >= 2`  |
| Red       | `$counts["black"] >= 2`|
| Black     | `$counts["red"] >= 2`  |
| Odd       | `$counts["even"] >= 2` |
| High      | `$counts["low"] >= 2`  |

- When inactive, the box shows `0`
- When active, the box shows a `£` stake amount
- Recommendations disappear when the triggering streak drops back below 2

---

## Feature 7: Stake Amount Lookup (SuperSafe Strategy)

- The component uses the `SuperSafe` betting strategy (hardcoded in `$behaviour`)
- The `SuperSafe` stake array is `[0, 0, 1, 1, 2, null, null, null]`
- Stake displayed = `stakes[streak_length - 1]` (0-indexed)
- `null` values in the array display as `x` (e.g., streak of 6 → index 5 → `£x`)
- An out-of-bounds index also displays as `x`
- The `$stakes` array is loaded from the database `Stake` record with `name = 'SuperSafe'`

| Streak | Index | SuperSafe value | Displayed |
|--------|-------|-----------------|-----------|
| 2      | 1     | 0               | £0        |
| 3      | 2     | 1               | £1        |
| 4      | 3     | 1               | £1        |
| 5      | 4     | 2               | £2        |
| 6      | 5     | null            | £x        |
| 7+     | 6+    | null / missing  | £x        |

---

## Feature 8: PossibleOutcome Reference Data (Seed Integrity)

- All 37 numbers (0–36) are seeded in the `possible_outcomes` table
- Number `0` has all six boolean properties set to `false`
- Red numbers are exactly: 1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36
- High numbers are exactly 19–36; Low numbers are exactly 1–18
- Even numbers are exactly the even integers 2–36; Odd numbers are 1–35
- For every non-zero number: red and black are mutually exclusive
- For every non-zero number: even and odd are mutually exclusive
- For every non-zero number: high and low are mutually exclusive

---

## Feature 9: Stake Reference Data (Seed Integrity)

- Exactly three strategies are seeded: `Plain`, `Safe`, `SuperSafe`
- Strategy names are unique
- `Plain` stakes: `[0, 1, 2, 4, 8, 16, 32, 64]`
- `Safe` stakes: `[0, 1, 1, 2, 4, null, null, null]`
- `SuperSafe` stakes: `[0, 0, 1, 1, 2, null, null, null]`
- Each `stakes` field contains valid JSON

---

## Out of Scope

- Authentication / User model (not used in this application)
- Theme toggle (client-side only, driven by Flux/Alpine localStorage)
- `Opposite` and `Trend` models (seeded but not used in current UI)
