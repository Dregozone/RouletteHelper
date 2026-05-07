# RouletteHelper

A single-page web application for tracking European roulette spins and generating counter-bet recommendations using a reverse-streak strategy. Built with Laravel, Livewire, and Flux UI.

Designed to be cloned and run locally, with no login required.

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [How to Use](#how-to-use)
- [Betting Strategies](#betting-strategies)
- [Running Tests](#running-tests)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [Bug Reporting](#bug-reporting)
- [License](#license)

---

## Overview

RouletteHelper tracks the history of a European roulette session (numbers 0–36) and analyses consecutive streaks across six bet types — **Red/Black**, **Odd/Even**, and **High/Low**. When an opposite bet type has been on a streak of two or more, the app surfaces a recommended counter-bet and calculates the appropriate stake based on the selected betting strategy.

The application has no authentication and requires no account. It is designed to be used at the table as a live session aid.

The logic runs on the assumption that a table is fair and even with rolls, if a coin is flipped 100 times it would be a fair assumption that in ideal conditions it will land on heads almost half of the time. The edge case being '0'/'00' rolls which would be the coin landing on its edge.

**Disclaimer: This tool is a bit of fun and intended for entertainment purposes only, although based in mathematics and statistical probabilities use it at your own risk.**

---

## Features

| # | Feature |
|---|---------|
| 1 | **Record a roll** — enter any number 0–36 and save it to the session history |
| 2 | **Delete individual rolls** — remove a specific spin from the history |
| 3 | **Clear all history** — wipe the entire session in one click |
| 4 | **Historical table** — shows the last 15 rolls with colour-coded Red/Black, Odd/Even, and High/Low labels |
| 5 | **Streak calculation** — tracks consecutive streaks for all six bet types across the last 15 rolls |
| 6 | **Betting recommendations** — six boxes activate when the opposing streak reaches 2 or more |
| 7 | **Stake lookup** — stake amounts are driven by a configurable strategy table stored in the database |

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.3+ |
| Framework | Laravel 13 |
| Frontend | Livewire 4, Flux UI 2, Alpine.js |
| Styling | Tailwind CSS 4 |
| Build tool | Vite 8 |
| Testing | Pest 4 |
| Code style | Laravel Pint |

---

## Prerequisites

Ensure the following are installed before proceeding:

- **PHP** 8.3 or higher (with the `pdo`, `mbstring`, `openssl`, and `sqlite`/`mysql` extensions)
- **Composer** 2.x
- **Node.js** 18+ and **npm** 9+
- A supported database — SQLite (zero-config, recommended for local use) or MySQL/PostgreSQL

---

## Installation & Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/dregozone/RouletteHelper.git
   cd RouletteHelper
   ```

2. **Run the one-command setup** (installs PHP & JS dependencies, generates app key, runs migrations, and builds frontend assets)

   ```bash
   composer run setup
   ```

   If you prefer to run each step manually:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

3. **Seed the reference data** (betting strategies and roulette outcomes)

   ```bash
   php artisan db:seed
   ```

---

## Configuration

All environment settings live in the `.env` file created during setup. Key variables:

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name shown in the browser tab | `RouletteHelper` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_URL` | Full base URL of the application | `http://localhost` |
| `DB_CONNECTION` | Database driver (`sqlite`, `mysql`, `pgsql`) | `sqlite` |
| `DB_DATABASE` | Path to the SQLite file or database name | `database/database.sqlite` |

For SQLite (the simplest local setup), no further database configuration is needed — the file is created automatically by `php artisan migrate`.

---

## Running the Application

**Development** (hot-reload with Vite):

```bash
composer run dev
```

This starts the Laravel development server, Vite HMR, and the queue worker concurrently.

**Production build** (compile and minify assets):

```bash
npm run build
php artisan serve
```

Then visit [http://localhost:8000](http://localhost:8000) in your browser.

---

## How to Use

1. **Enter a number** (0–36) in the input field and click **Save** to record a spin.
2. The **historical table** updates immediately, showing the last 15 spins with colour-coded labels for each bet property.
3. The **recommendation boxes** at the top of the page activate automatically when a streak of 2 or more is detected for the opposing bet type — each box shows the recommended stake for the current streak length.
4. Click the **delete icon** on any row to remove that spin from history.
5. Click **Clear all** to reset the session completely.

> **Note:** Number `0` (the green pocket) breaks all active streaks immediately and is displayed with neutral `—` labels in the history table.

---

## Betting Strategies

Stake amounts are driven by strategy records in the database. Three strategies are seeded out of the box:

| Strategy | Stake array | Description |
|----------|-------------|-------------|
| `Plain` | `[0, 1, 2, 4, 8, 16, 32, 64]` | Standard Martingale-style doubling |
| `Safe` | `[0, 1, 1, 2, 4, null, null, null]` | Conservative escalation |
| `SuperSafe` | `[0, 0, 1, 1, 2, null, null, null]` | Very conservative; default strategy |

The stake displayed for a given recommendation equals `stakes[streak_length - 1]`. A `null` entry or an out-of-bounds index renders as **£x**, indicating the streak has exceeded the configured range.

The active strategy is hardcoded to `SuperSafe` in the component. To change it, update the `$behaviour` property in the main Livewire component.

---

## Running Tests

The full test suite uses [Pest](https://pestphp.com/):

```bash
php artisan test --compact
```

Run a specific test file or filter by name:

```bash
php artisan test --compact --filter=RecordARollTest
php artisan test --compact --filter="recording a roll creates a new historical record"
```

All tests use an in-memory SQLite database and do not affect your local data.

---

## Project Structure

```
app/
  Livewire/         # Livewire page components
  Models/           # Eloquent models (Historical, PossibleOutcome, Stake, ...)
database/
  migrations/       # Schema definitions
  seeders/          # Reference data (possible outcomes, stakes)
resources/
  views/
    pages/          # Blade views for Livewire pages
    components/     # Reusable Blade components
docs/
  feature-list.md   # Canonical feature specification
  test-plan.md      # Test-by-test documentation
tests/
  Feature/          # Pest feature tests
```

---

## Contributing

Contributions are welcome. Please follow these steps:

1. Fork the repository and create a feature branch from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
2. Make your changes, following the existing code conventions.
3. Add or update tests to cover your changes — every change must be tested.
4. Run the test suite and ensure it passes:
   ```bash
   php artisan test --compact
   ```
5. Run the code formatter before committing:
   ```bash
   vendor/bin/pint --dirty
   ```
6. Open a pull request with a clear description of the change and the problem it solves.

Please keep pull requests focused — one feature or fix per PR.

---

## Bug Reporting

Found a bug? Please [open an issue](https://github.com/dregozone/RouletteHelper/issues/new) and include:

- A clear description of the unexpected behaviour
- Steps to reproduce the issue
- The number(s) you entered and the resulting state of the recommendation boxes
- Your PHP version (`php -v`) and browser

Feature requests and suggestions are also welcome via GitHub Issues.

---

## License

This project is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
