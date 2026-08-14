#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
COMPOSE_FILE="$ROOT_DIR/tests/wordpress/docker-compose.yml"
COMPOSE_DIR="$(dirname "$COMPOSE_FILE")"
WORDPRESS_VERSION="${WORDPRESS_VERSION:-7.0}"
WORDPRESS_PHP_VERSION="${WORDPRESS_PHP_VERSION:-8.4}"
WORDPRESS_HTTP_PORT="${WORDPRESS_HTTP_PORT:-8080}"
WORDPRESS_URL="${WORDPRESS_URL:-http://localhost:${WORDPRESS_HTTP_PORT}}"
FIXTURE_DIR="$ROOT_DIR/tests/wordpress/fixtures/monolog-wp-cli-smoke"
PLUGIN_DST_DIR="/var/www/html/wp-content/plugins/monolog-wp-cli-smoke"
WP=(docker compose --project-directory "$COMPOSE_DIR" -f "$COMPOSE_FILE" run --rm wpcli wp)
COMPOSE=(docker compose --project-directory "$COMPOSE_DIR" -f "$COMPOSE_FILE")

echo "Setting up WordPress ${WORDPRESS_VERSION} on PHP ${WORDPRESS_PHP_VERSION}."

(
  cd "$FIXTURE_DIR"
  composer update mhcg/monolog-wp-cli --no-dev --no-interaction
)

"${WP[@]}" core is-installed >/dev/null 2>&1 || "${WP[@]}" core install \
  --url="$WORDPRESS_URL" \
  --title="Monolog WP-CLI Smoke" \
  --admin_user=admin \
  --admin_password=password \
  --admin_email=admin@example.com \
  --skip-email

"${COMPOSE[@]}" exec -T wordpress rm -rf "$PLUGIN_DST_DIR"
"${COMPOSE[@]}" exec -T wordpress mkdir -p "$PLUGIN_DST_DIR"
tar -C "$FIXTURE_DIR" -cf - . | "${COMPOSE[@]}" exec -T -i wordpress tar -C "$PLUGIN_DST_DIR" -xf -

"${WP[@]}" plugin activate monolog-wp-cli-smoke >/dev/null

echo "wordpress-setup-ok"
