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

function mycommand_command( $args )
{
    $logger = new Logger( 'mycommand' );
    $logger->pushHandler( new WPCLIHandler( Logger::INFO ) );

    $logger->info( 'Starting' );
    $logger->warning( 'Potential issue detected' );
    $logger->error( 'Failed to process one item' );
}

WP_CLI::add_command( 'mycommand', 'mycommand_command' );
```

3. Run the command normally:

```shell
wp mycommand
```

4. Optional: run with debug visibility:

```shell
wp mycommand --debug
```

5. Optional: run in quiet mode:

```shell
wp mycommand --quiet
```

## Notes

- The handler is intended for WP-CLI runtime. Constructing it outside WP-CLI raises a runtime exception.
- `debug` messages rely on WP-CLI debug mode.
- Error-level output is sent through WP-CLI error handling.