#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/tests/wordpress/docker-compose.yml"
COMPOSE_DIR="$(dirname "$COMPOSE_FILE")"
WORDPRESS_VERSION="${WORDPRESS_VERSION:-7.0}"
WORDPRESS_PHP_VERSION="${WORDPRESS_PHP_VERSION:-8.4}"
WORDPRESS_HTTP_PORT="${WORDPRESS_HTTP_PORT:-8080}"
WORDPRESS_URL="${WORDPRESS_URL:-http://localhost:${WORDPRESS_HTTP_PORT}}"
PLUGIN_SRC="$ROOT_DIR/tests/wordpress/fixtures/monolog-wp-cli-smoke/monolog-wp-cli-smoke.php"
PLUGIN_DST_DIR="/var/www/html/wp-content/plugins/monolog-wp-cli-smoke"
PROJECT_DST_DIR="/workspaces/monolog-wp-cli"
WP=(docker compose --project-directory "$COMPOSE_DIR" -f "$COMPOSE_FILE" run --rm wpcli wp)
COMPOSE=(docker compose --project-directory "$COMPOSE_DIR" -f "$COMPOSE_FILE")

echo "Setting up WordPress ${WORDPRESS_VERSION} on PHP ${WORDPRESS_PHP_VERSION}."

"${WP[@]}" core is-installed >/dev/null 2>&1 || "${WP[@]}" core install \
  --url="$WORDPRESS_URL" \
  --title="Monolog WP-CLI Smoke" \
  --admin_user=admin \
  --admin_password=password \
  --admin_email=admin@example.com \
  --skip-email

"${COMPOSE[@]}" exec -T wordpress mkdir -p "$PROJECT_DST_DIR"
"${COMPOSE[@]}" exec -T wordpress rm -rf "$PROJECT_DST_DIR/src" "$PROJECT_DST_DIR/vendor"
"${COMPOSE[@]}" cp "$ROOT_DIR/src" "wordpress:$PROJECT_DST_DIR/src"
"${COMPOSE[@]}" cp "$ROOT_DIR/vendor" "wordpress:$PROJECT_DST_DIR/vendor"

"${COMPOSE[@]}" exec -T wordpress mkdir -p "$PLUGIN_DST_DIR"
"${COMPOSE[@]}" cp "$PLUGIN_SRC" "wordpress:$PLUGIN_DST_DIR/monolog-wp-cli-smoke.php"

"${WP[@]}" plugin activate monolog-wp-cli-smoke >/dev/null

echo "wordpress-setup-ok"
