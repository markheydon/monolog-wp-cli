#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/tests/wordpress/docker-compose.yml"
COMPOSE_DIR="$(dirname "$COMPOSE_FILE")"
WP=(docker compose --project-directory "$COMPOSE_DIR" -f "$COMPOSE_FILE" run --rm wpcli wp)

assert_contains() {
  local needle="$1"
  local haystack="$2"
  local message="$3"
  if [[ "$haystack" != *"$needle"* ]]; then
    echo "$message" >&2
    echo "$haystack" >&2
    exit 1
  fi
}

NOTICE_OUTPUT="$("${WP[@]}" monolog-smoke --level=notice --message='notice from wp' 2>&1)"
assert_contains "(NOTICE) notice from wp" "$NOTICE_OUTPUT" "NOTICE should route via WP_CLI::log and include level."

WARNING_OUTPUT="$("${WP[@]}" monolog-smoke --level=warning --message='warning from wp' 2>&1)"
assert_contains "Warning: (WARNING) warning from wp" "$WARNING_OUTPUT" "WARNING should route via WP_CLI::warning."

ERROR_OUTPUT="$("${WP[@]}" monolog-smoke --level=error --message='error from wp' 2>&1)"
assert_contains "Error: (ERROR) error from wp" "$ERROR_OUTPUT" "ERROR should route via WP_CLI::error."

set +e
CRITICAL_OUTPUT="$("${WP[@]}" monolog-smoke --level=critical --message='critical from wp' 2>&1)"
CRITICAL_EXIT=$?
set -e

if [[ $CRITICAL_EXIT -eq 0 ]]; then
  echo "CRITICAL should exit non-zero." >&2
  echo "$CRITICAL_OUTPUT" >&2
  exit 1
fi
assert_contains "Error: (CRITICAL) critical from wp" "$CRITICAL_OUTPUT" "CRITICAL should route via WP_CLI::error and exit."

QUIET_OUTPUT="$("${WP[@]}" --quiet monolog-smoke --level=info --message='quiet info check' 2>&1 || true)"
if [[ "$QUIET_OUTPUT" == *"quiet info check"* ]]; then
  echo "INFO output should be suppressed with --quiet." >&2
  echo "$QUIET_OUTPUT" >&2
  exit 1
fi

DEBUG_OUTPUT="$("${WP[@]}" --debug monolog-smoke --level=debug --message='debug from wp' 2>&1)"
assert_contains "debug from wp" "$DEBUG_OUTPUT" "DEBUG output should be visible with --debug."

echo "wp-smoke-ok"
