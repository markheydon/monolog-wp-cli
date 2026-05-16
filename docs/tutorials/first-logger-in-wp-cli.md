# Tutorial: First Logger in WP-CLI

This tutorial walks through a minimal setup that sends Monolog output to WP-CLI.

## Goal

By the end, you will run a command and see warning and error output through WP-CLI.

## Before you start

- A project where WP-CLI commands run.
- Composer available.

## 1. Install the package

```shell
composer require mhcg/monolog-wp-cli
```

## 2. Create a command callback

Use Monolog with the handler and emit a few levels:

```php
<?php

use Monolog\Logger;
use MHCG\Monolog\Handler\WPCLIHandler;

function mycommand_command( $args ) {
    $log = new Logger( 'mycommand' );
    $log->pushHandler( new WPCLIHandler( Logger::INFO ) );

    $log->debug( 'Only shown with --debug' );
    $log->info( 'Started running' );
    $log->warning( 'Something happened of note' );
    $log->error( 'An error has occurred' );
}

WP_CLI::add_command( 'mycommand', 'mycommand_command' );
```

## 3. Run the command

```shell
wp mycommand
```

Expected behaviour:

- `debug` is hidden unless `--debug` is used.
- `info` and `warning` are normal command output.
- `error` appears as an error message.

## 4. Check quiet mode

```shell
wp mycommand --quiet
```

Expected behaviour:

- Normal output is suppressed.
- Error output still appears.

## Next step

If you already have an existing command and only need integration steps, continue with the [how-to guide](../how-to/use-wpclihandler-in-a-command.md).