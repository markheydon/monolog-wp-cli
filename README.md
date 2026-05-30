# Monolog WP-CLI Handler

[![Packagist Version](https://img.shields.io/packagist/v/mhcg/monolog-wp-cli.svg)](https://packagist.org/packages/mhcg/monolog-wp-cli)
[![PHP CI](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml)

Extension for [Monolog](https://github.com/Seldaek/monolog) that routes log output through WP-CLI when running `wp` commands.

## Installation

```shell
composer require mhcg/monolog-wp-cli
```

## Requirements

- PHP `^7.2 || ^8.0`
- monolog/monolog `^2.5`

Current stable releases target the Monolog 2 line. Monolog 3 support remains planned for a future major release.

Official WordPress-runtime support is maintained separately from the package runtime floor. The current policy supports the current and previous WordPress major series through an explicit smoke-test tuple list rather than a blanket WordPress/PHP cross-product claim.

## Usage

`WPCLIHandler` works like any other Monolog handler. Create the handler and push it onto a logger inside a WP-CLI command context.

By default the handler uses message-only output. Monolog `context` and `extra` data are only rendered when verbose formatting is enabled, either by passing `true` as the third `WPCLIHandler` constructor argument or when `WP_DEBUG` is enabled.

### Version note (v2.2)

From v2.2, default `NOTICE` handling changed from `WP_CLI::warning()` to `WP_CLI::log()`.

- Before v2.2: `notice` messages were shown as `Warning:` output.
- From v2.2: `notice` messages are logged as normal output with `(NOTICE)` level prefix.

If you need previous behaviour, pass a custom logger map override as the fourth `WPCLIHandler` constructor argument:

```php
$log->pushHandler(
    new WPCLIHandler(
        Logger::INFO,
        true,
        false,
        [
            Logger::NOTICE => [
                'method' => 'warning',
                'includeLevelName' => true,
            ],
        ]
    )
);
```

```php
<?php

use Monolog\Logger;
use MHCG\Monolog\Handler\WPCLIHandler;

// Create a log channel.
$log = new Logger('name');
$log->pushHandler(new WPCLIHandler(Logger::WARNING));

// Output to WP-CLI.
$log->warning('This is a warning');
$log->error('An error has occurred');
$log->critical('This will report error and exit out');
$log->debug('Only shown when running wp with --debug');
$log->info('General logging - will not be shown when running wp with --quiet');
```

If you want Monolog `context` and `extra` data included in the output, enable verbose formatting:

```php
$log->pushHandler(new WPCLIHandler(Logger::WARNING, true, true));
$log->warning('This includes context in verbose mode', ['job' => 'import']);
```

### WordPress plugin-style example

The following example registers a `mycommand` WP-CLI command that writes log output through the handler.

This demonstrates the handler only. It is not a recommended project structure for building plugins or commands.

```php
<?php
/**
 * Plugin Name:     My Plugin
 */

// my-plugin.php

use Monolog\Logger;
use MHCG\Monolog\Handler\WPCLIHandler;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Autoload.
$autoload = dirname( __FILE__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

// 'mycommand' WP-CLI command.
if ( defined( 'WP_CLI' ) && WP_CLI ) {

    function mycommand_command( $args ) {
        // Create logger.
        $log = new Logger( 'name' );
        $log->pushHandler( new WPCLIHandler( Logger::INFO ) );

        // Will only show when wp is run with --debug.
        $log->debug( 'Some geeky stuff' );

        // These will not show when wp is run with --quiet.
        $log->info( 'Started running' );
        $log->warning( 'Something happened of note' );

        // Always shows even with --quiet.
        $log->error( 'An error has occurred' );

        // No direct Monolog equivalent of WP_CLI::success.
        WP_CLI::success( 'Finished running mycommand' );
    }

    WP_CLI::add_command( 'mycommand', 'mycommand_command' );

}
```

```shell
wp mycommand
Started running
Warning: (WARNING) Something happened of note
Error: (ERROR) An error has occurred
Success: Finished running mycommand
```

```shell
wp mycommand --quiet
Error: (ERROR) An error has occurred
```

For full mapping and override details, see [WPCLIHandler reference](docs/reference/wpclihandler.md).

## Development

Install dependencies:

```shell
composer install
```

Run local checks:

```shell
composer run test
composer run test:runtime-smoke
composer run test:wp
composer run lint
composer run qa
```

Run WordPress integration checks step-by-step:

```shell
composer run wp:env:up
composer run test:wp:setup
composer run test:wp:smoke
composer run wp:env:down
```

Note: `composer run test:wp` does not call `wp:env:down`; run teardown explicitly when you are finished.

The local WordPress smoke workflow defaults to the repository's current baseline tuple. To run a different supported tuple, set `WORDPRESS_VERSION` and `WORDPRESS_PHP_VERSION` before running the WordPress scripts.

Example:

```shell
WORDPRESS_VERSION=6.8 WORDPRESS_PHP_VERSION=8.4 composer run test:wp
```

CI runs on pull requests and pushes to `main`.

- `PHP CI` validates Composer metadata, runs runtime smoke checks on PHP 7.2 to 8.5, runs unit tests across PHP 7.2 to 8.5 using a compatible PHPUnit line per PHP version, runs dependency audit, PHPMD, and PHPCS on PHP 8.3, and runs a dedicated WordPress smoke matrix for the repository's officially supported WordPress/PHP tuples.

For the reasoning behind the support window and tuple-based WordPress policy, see [Compatibility and release-line policy](docs/explanation/compatibility-and-release-line-policy.md).

## Testing and code quality

- PHPUnit runs the test suite from `tests/`.
- PHPMD checks `src/` for code-size and unused-code issues.
- PHPCS enforces PSR-12 across `src/` and `tests/`.

## Contributing

Fork the repository and open a pull request for code changes, referencing the related issue where relevant. For maintainer expectations, see [CONTRIBUTING](.github/CONTRIBUTING.md).

Documentation improvements are also welcome through the project wiki.

Please follow the [Code of Conduct](.github/CODE_OF_CONDUCT.md).

## Support

- Issues: <https://github.com/markheydon/monolog-wp-cli/issues>
- Wiki: <https://github.com/markheydon/monolog-wp-cli/wiki>
- Source: <https://github.com/markheydon/monolog-wp-cli>

## Licence

Released under the MIT licence. See [LICENSE](LICENSE) for details.
