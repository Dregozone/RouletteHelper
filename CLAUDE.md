<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

<!-- cloud-worker:start (kept by Life OS: docs/office/cloud-worker/CLAUDE-section.md; edit it there) -->
# Working on an issue in the cloud

These rules apply when you're in a Claude Code **cloud session** (`CLAUDE_CODE_REMOTE` is `true`),
started from Life OS's "☁ Cloud worker" to work on a GitHub issue. The person reads your work on
their phone and may be away, so be thorough and don't stop for things you can decide yourself.

1. **Read the issue first.** Use your GitHub tools to read the issue named in the first message,
   with all its comments. If you can't read it, stop and say so: never guess from the title.
2. **Make a branch** from the default branch, named `issue-<number>-<short-title>` (for example
   `issue-17-birthday-reminders`). If pushing that name is refused, use the branch the session
   gives you; the pull request still links the issue.
3. **Do the work** the way this repository does things (the rest of this file, and its docs).
   The session's startup hook installed the dependencies; its line starts "Cloud setup (Life OS)".
   - If a step says FAILED, run the hook again once: `CLAUDE_CODE_REMOTE=true bash .claude/hooks/cloud-setup.sh`
     (its log is `/tmp/cloud-setup.log`).
   - **Spend at most 10 minutes on setup in all.** Don't invent your own install. If the tests
     still can't run, do the work anyway, and say plainly in the pull request (or, in a run of
     several issues, in a comment on the issue) that the tests weren't run, and why.
   - In every pull request, give the hook's line (it says how many seconds setup took), and say
     whether anything failed.
4. **Run the tests** (`php artisan test --compact`, plus `npm run build` if you changed the front
   end), and fix what you broke. Use SQLite for tests: there's no MySQL here.
5. **Bring the branch up to date** before the pull request: fetch, rebase onto the default branch,
   and run the tests again.
6. **Open the pull request yourself**, ready for review (not a draft):
   - Use your GitHub tools, or `gh api` (REST). `gh pr create` and `gh issue` don't work here
     (GitHub's GraphQL is blocked). For example:
     `gh api repos/OWNER/REPO/pulls -f title="…" -f head="issue-17-…" -f base="main" -f body="…"`
   - The description says what changed and why. Include an **Assumptions** section listing any
     choice you made without asking. End the description with `Closes #<number>`.
   - Where it helps the review (a visible change), add screenshots: commit them under
     `.github/pr-images/` on the branch and embed each with
     `![what it shows](https://github.com/OWNER/REPO/blob/COMMIT_SHA/.github/pr-images/NAME.png?raw=true)`.
7. **Check for overlap:** list the other open pull requests' files (`gh api repos/OWNER/REPO/pulls`
   and `…/pulls/N/files`). If any touch the same files, add a line to your description such as
   "Overlaps #12 in routes/web.php: either can merge first". Never push to another pull request's
   branch.
8. **Questions:** only ask when a real decision is needed that you can't sensibly make (the person
   answers from a phone). Otherwise choose, and list it under Assumptions.
9. **Never** print credentials, tokens or environment variables, and never commit `.env` or
   `auth.json`.

# Working through several issues in the cloud

When the first message lists several issues ("Work through these issues: #3, #5, #8"), the person
has asked for all of them in this one session, as **one pull request per group of related issues**.
They have sent it off and are not watching, so never stop to ask: everything above still applies to
each group, with these changes.

1. **Read every listed issue** and its comments. Leave out, and say why, any that is closed, that
   already has an open pull request (look for `Closes #N` in open pull requests' descriptions), or
   that you can't read.
2. **Group them:** issues go in one pull request when they change the same feature or the same
   files, at most 4 per pull request. Every other issue gets its own. Order the groups smallest and
   most independent first.
3. **Write the plan as your first message** (each group with its issues, the order, anything left
   out) and carry straight on. Don't wait for an answer.
4. **One branch per group, each from the up-to-date default branch** (`git fetch origin`, then
   `git switch -c issue-<first number>-<short-title> origin/<default>`), so every pull request
   stands alone and they can merge in any order. The person has asked for a separate branch and
   pull request for each group: push each group to its own branch, even if the session suggested a
   single working branch. Only if pushing a second branch is actually refused, stop starting new
   groups and say so in the summary.
5. **For each group**, follow steps 3–7 above. End its description with one `Closes #N` line per
   issue. Before the next group, go back to the default branch with a clean working tree: nothing
   from one group may end up in the next.
6. **When a group goes wrong** (tests you can't make pass, or a decision only the person can make):
   don't open a pull request for it and don't wait. Post a comment on each of its issues
   (`gh api repos/OWNER/REPO/issues/N/comments -f body="…"`) saying what blocked you or what needs
   deciding, with the options you see. Then go on to the next group.
7. **Finish with a summary:** each pull request (number, link, the issues it closes), and each issue
   not done, with why.

Outside a cloud session (at a desk on the PC), Life OS gives its own instructions instead: follow
those.
<!-- cloud-worker:end -->
