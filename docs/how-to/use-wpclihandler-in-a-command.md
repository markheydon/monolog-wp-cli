# How-to: Use WPCLIHandler in a Command

Use this guide when you already have a WP-CLI command and want to route logs through Monolog.

## Steps

1. Install the package:

```shell
composer require mhcg/monolog-wp-cli
```

2. In your command callback, create a logger and attach the handler:

```php
<?php

use Monolog\Logger;
use MHCG\Monolog\Handler\WPCLIHandler;

function mycommand_command( $args ) {
    $logger = new Logger( 'mycommand' );
    $logger->pushHandler( new WPCLIHandler( Logger::INFO ) );

    $logger->info( 'Starting' );
    $logger->warning( 'Potential issue detected' );
    $logger->error( 'Failed to process one item' );
}

WP_CLI::add_command( 'mycommand', 'mycommand_command' );
```

3. Optional: if you need pre-v2.2 `NOTICE` behaviour (`warning` output), pass a logger-map override:

```php
$logger->pushHandler(
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

4. Optional: if you want Monolog `context` and `extra` data in output, enable verbose mode:

```php
$logger->pushHandler( new WPCLIHandler( Logger::INFO, true, true ) );
$logger->notice( 'Processed batch', [ 'batch' => 12 ] );
```

You can also enable the same formatter behaviour through `WP_DEBUG`.

5. Run the command normally:

```shell
wp mycommand
```

6. Optional: run with debug visibility:

```shell
wp mycommand --debug
```

7. Optional: run in quiet mode:

```shell
wp mycommand --quiet
```

## Notes

- The handler is intended for WP-CLI runtime. Constructing it outside WP-CLI raises a runtime exception.
- `debug` messages rely on WP-CLI debug mode.
- Monolog `context` and `extra` data are only shown when the handler is in verbose mode, either via the constructor flag or `WP_DEBUG`.
- From v2.2, `notice` maps to `WP_CLI::log()` by default.
- Error-level output is sent through WP-CLI error handling.