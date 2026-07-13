# How-to: Test under WordPress and WP-CLI

Use this guide when you want to verify behaviour in a real WordPress runtime, not only unit tests.

If you prefer a single command, run `composer run test:wp`.
It performs `wp:env:up`, `test:wp:setup`, and `test:wp:smoke`.

## Prerequisites

- Docker with Compose support.
- Project dependencies installed:

```shell
composer install
```

## Start the local WordPress test environment

```shell
composer run wp:env:up
composer run test:wp:setup
```

This starts `db` and `wordpress` containers, then initialises WordPress and activates the smoke fixture plugin used for testing.

By default, the local workflow uses the repository's current baseline smoke tuple. You can select another supported tuple by setting both `WORDPRESS_VERSION` and `WORDPRESS_PHP_VERSION` before running the scripts.

Currently supported tuples:

- WordPress `7.0` on PHP `8.4`
- WordPress `7.0` on PHP `8.5`
- WordPress `6.8` on PHP `8.3`
- WordPress `6.8` on PHP `8.4`

Example:

```shell
WORDPRESS_VERSION=6.8 WORDPRESS_PHP_VERSION=8.4 composer run test:wp
```

## Run the integration smoke checks

```shell
composer run test:wp:smoke
```

The smoke checks validate:

- `NOTICE` routes through `WP_CLI::log()` with level prefix.
- `WARNING` routes through `WP_CLI::warning()`.
- `ERROR` routes through `WP_CLI::error()` without requiring immediate process exit.
- `CRITICAL` routes through `WP_CLI::error()` and exits non-zero.
- `INFO` output is suppressed in `--quiet` runs.
- `DEBUG` output appears when running with `--debug`.

## Official support window and smoke coverage

The repository keeps WordPress-runtime support separate from package runtime compatibility.

- Package runtime compatibility comes from `composer.json` and the active Monolog line.
- Official WordPress-runtime support is maintained through explicit WordPress/PHP smoke-test tuples.

Current policy snapshot for WordPress smoke coverage:

- WordPress `7.0` on PHP `8.4`
- WordPress `7.0` on PHP `8.5`
- WordPress `6.8` on PHP `8.3`
- WordPress `6.8` on PHP `8.4`

That tuple list is what CI is expected to exercise. Local runs may use the default tuple or any other supported tuple by setting environment variables.

## Tear down environment

```shell
composer run wp:env:down
```

Use this after local testing to remove containers and volumes.

Note: `composer run test:wp` does not run teardown automatically.

## Notes

- The fixture command is registered as `wp monolog-smoke`.
- Fixture source is at `tests/wordpress/fixtures/monolog-wp-cli-smoke/monolog-wp-cli-smoke.php`.
- Integration scripts are at `tests/wordpress/bin/`.
- The Docker image tags are derived from `WORDPRESS_VERSION` and `WORDPRESS_PHP_VERSION`.
- When a new WordPress major release lands, refresh both the tuple list and the CI matrix together rather than updating only one side.
