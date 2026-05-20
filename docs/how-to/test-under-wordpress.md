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
