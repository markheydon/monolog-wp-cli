# Monolog WP-CLI Handler

[![Packagist Version](https://img.shields.io/packagist/v/mhcg/monolog-wp-cli.svg)](https://packagist.org/packages/mhcg/monolog-wp-cli)
[![PHP CI](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml)

Extension for [Monolog](https://github.com/Seldaek/monolog) that routes log output through WP-CLI when running `wp` commands.

## Requirements

- PHP 8.3+
- Monolog 2.5

## Installation

```shell
composer require mhcg/monolog-wp-cli
```

## Usage

`WPCLIHandler` works like any other Monolog handler. Create the handler and push it onto a logger inside a WP-CLI command context.

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

## Development

Install dependencies:

```shell
composer install
```

Run local checks:

```shell
composer run test
composer run lint
composer run qa
```

CI runs on pull requests and pushes to main, validates Composer metadata, audits locked dependencies, runs PHPUnit on PHP 8.3 and 8.4, and runs PHPMD and PHPCS on PHP 8.3.

## Testing and code quality

- PHPUnit runs the test suite from tests.
- PHPMD checks src for code-size and unused-code issues.
- PHPCS enforces PSR-12 across src and tests.

## Contributing

Fork the repository and open a pull request for code changes, referencing the related issue where relevant. Documentation improvements are also welcome through the wiki.

Please follow the [Code of Conduct](https://github.com/markheydon/monolog-wp-cli/blob/main/.github/CODE_OF_CONDUCT.md).

## Support

- Issues: <https://github.com/markheydon/monolog-wp-cli/issues>
- Wiki: <https://github.com/markheydon/monolog-wp-cli/wiki>
- Source: <https://github.com/markheydon/monolog-wp-cli>

## Licence

Released under the MIT licence. See [LICENSE](LICENSE) for details.
