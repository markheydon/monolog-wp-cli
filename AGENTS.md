# AGENTS.md

## Cursor Cloud specific instructions

This repository is a PHP library (`mhcg/monolog-wp-cli`): a Monolog handler that
routes log output through WP-CLI. There is no long-running application service —
"running it" means exercising the handler via the unit tests, the runtime smoke
script, and the WordPress/WP-CLI smoke workflow.

The commands below are the source of truth in `composer.json` (`scripts`),
`README.md` (`## Development`), and `.github/workflows/php.yml`. Prefer those.
The update script already installs PHP 8.3, Composer, Docker, and runs
`composer install`, so dependencies are ready when a session starts.

### Core checks (no Docker required)

- `composer run test` — PHPUnit unit suite (`tests/`).
- `composer run test:runtime-smoke` — standalone script that mocks `WP_CLI` and
  asserts level routing.
- `composer run lint` — PHPMD (`src/`) + PHPCS (PSR-12 across `src/` and `tests/`).
- `composer run qa` — runs `test` + `lint`.

### WordPress smoke workflow (needs Docker)

The `test:wp*` and `wp:env:*` scripts use `docker compose` with `tests/wordpress/docker-compose.yml`
(MariaDB + WordPress + WP-CLI). Non-obvious gotchas:

- Docker is NOT auto-started on session boot. Start the daemon first, e.g.
  `sudo dockerd` (running it in a background/tmux session works well). The daemon
  is configured for `fuse-overlayfs` with the containerd snapshotter disabled in
  `/etc/docker/daemon.json` — this is required for Docker 29 to work in this VM.
- The `ubuntu` user is in the `docker` group, but a shell started before the
  daemon/group existed may not have it applied. If `docker ps` gives a permission
  error, prefix commands with `sg docker -c '...'` (a fresh login shell picks the
  group up automatically).
- `test:wp:setup` requires `rsync` (installed by the update script) and runs
  `composer update` inside `tests/wordpress/fixtures/monolog-wp-cli-smoke`, which
  mirrors the local package into the WordPress container as a plugin.
- Full flow: `composer run wp:env:up`, `composer run test:wp:setup`,
  `composer run test:wp:smoke`, then `composer run wp:env:down` to tear down.
  `composer run test:wp` chains the first three but does NOT tear down.
- Override the tuple with `WORDPRESS_VERSION` / `WORDPRESS_PHP_VERSION` env vars
  (defaults: WordPress 7.0 on PHP 8.4).

### Manual "hello world"

Route a Monolog log through a real WP-CLI command (after `wp:env:up` + `test:wp:setup`):

```
cd tests/wordpress
docker compose -f docker-compose.yml run --rm --entrypoint wp wpcli \
  monolog-smoke --level=notice --message="hello world"
# -> (NOTICE) hello world
```
