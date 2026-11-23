#!/bin/bash
# Claude Code cloud sessions only (Life OS's cloud worker, docs/office/30 in life-os): install what
# this Laravel project needs when a cloud session starts. Does nothing on your own computer.
# Kept by Life OS (docs/office/cloud-worker/cloud-setup.sh); edit it there.
#
# Flux Pro comes through the cloud environment's network secret for composer.fluxui.dev (Basic):
# no auth.json is needed or made here.
[ "${CLAUDE_CODE_REMOTE:-}" = "true" ] || exit 0
cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0

LOG=/tmp/cloud-setup.log
: > "$LOG"
said=()
step() {
  # step "what" command...: runs it, logs it, and remembers how it went.
  local what="$1"; shift
  echo "== $what" >> "$LOG"
  if "$@" >> "$LOG" 2>&1; then said+=("ok: $what"); else said+=("FAILED: $what (see $LOG)"); fi
}

said+=("$(php -r 'echo "PHP ", PHP_VERSION;' 2>/dev/null || echo 'PHP missing')")

# The extensions every project needs. The environment's setup script installs them, but a rebuilt
# environment once came without xml and sqlite3 (orbit, 23 Nov): add any missing (sessions run as root).
php_extensions() {
  local v missing=()
  v="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')" || return 1
  for ext in mbstring xml dom curl zip pdo_sqlite bcmath intl; do
    php -m | grep -qix "$ext" || missing+=("$ext")
  done
  [ ${#missing[@]} -eq 0 ] && return 0
  echo "missing: ${missing[*]}"
  local pkgs=()
  for ext in "${missing[@]}"; do
    case "$ext" in dom) pkgs+=("php$v-xml") ;; pdo_sqlite) pkgs+=("php$v-sqlite3") ;; *) pkgs+=("php$v-$ext") ;; esac
  done
  DEBIAN_FRONTEND=noninteractive apt-get install -y -qq $(printf '%s\n' "${pkgs[@]}" | sort -u) \
    || { apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y -qq $(printf '%s\n' "${pkgs[@]}" | sort -u); }
}
command -v php >/dev/null && step "PHP extensions" php_extensions

composer_install() {
  # CLOUD_SETUP_SKIP_ZIPS=1 (the weekly GitHub check) goes straight to the git builder below.
  if [ "${CLOUD_SETUP_SKIP_ZIPS:-}" != "1" ]; then
    composer install --no-interaction --prefer-dist --no-progress && return 0
  fi
  # GitHub's zips of other repositories are refused in cloud sessions (its proxy only serves the
  # repositories attached to the session); git clones work. Zip-only packages (phpstan) are built
  # from git by cloud-dists.php into a temporary copy of the lock file; the real one is untouched.
  echo "== zip downloads failed: building GitHub's zips from git"
  local made=0 code=0
  grep -qx 'composer.cloud.*' .git/info/exclude 2>/dev/null || echo 'composer.cloud.*' >> .git/info/exclude 2>/dev/null
  if [ -f .claude/hooks/cloud-dists.php ]; then made="$(php .claude/hooks/cloud-dists.php)"; fi
  if [ "${made:-0}" != "0" ] && [ -f composer.cloud.json ]; then
    # The zips are local now; a package that couldn't be built still comes from git.
    COMPOSER=composer.cloud.json composer install --no-interaction --prefer-dist --no-progress || code=$?
    rm -f composer.cloud.json composer.cloud.lock
  else
    composer install --no-interaction --prefer-source --no-progress || code=$?
  fi
  return $code
}

if [ -f composer.json ]; then
  if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/autoload.php ]; then
    step "composer install" composer_install
  else
    said+=("ok: composer packages already installed")
  fi
fi

if [ -f package-lock.json ]; then
  if [ ! -d node_modules ] || [ package-lock.json -nt node_modules ]; then
    step "npm ci" npm ci --no-audit --no-fund
  else
    said+=("ok: npm packages already installed")
  fi
fi

if [ -f artisan ]; then
  [ -f .env ] || cp .env.example .env 2>/dev/null
  # Judged by the key landing in .env: orbit's key:generate set the key yet exited non-zero.
  key_generate() { php artisan key:generate --no-interaction --force; grep -q '^APP_KEY=base64' .env; }
  if [ -f .env ] && ! grep -q '^APP_KEY=base64' .env; then step "artisan key:generate" key_generate; fi
  # Feature tests render pages that need Vite's manifest.
  if [ -f package.json ] && [ ! -f public/build/manifest.json ]; then step "npm run build" npm run build; fi
fi

# What Claude sees at the start of the session.
echo "Cloud setup (Life OS), ${SECONDS}s: ${said[*]}"
exit 0
