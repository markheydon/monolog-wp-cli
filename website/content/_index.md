---
title: Monolog WP-CLI Handler
---

[![Packagist Version](https://img.shields.io/packagist/v/mhcg/monolog-wp-cli.svg)](https://packagist.org/packages/mhcg/monolog-wp-cli)
[![PHP CI](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/markheydon/monolog-wp-cli/actions/workflows/php.yml)

Extension for [Monolog](https://github.com/Seldaek/monolog) that routes log output through WP-CLI when running `wp` commands.

## Install

```shell
composer require mhcg/monolog-wp-cli
```

## Requirements

- PHP `^8.1`
- monolog/monolog `^3.0`

The v2.x branch is maintained separately for Monolog 2 users.

## Documentation

| Guide | Description |
|-------|-------------|
| [First logger in WP-CLI](/docs/tutorials/first-logger-in-wp-cli/) | Step-by-step setup for a minimal working logger. |
| [Use WPCLIHandler in a command](/docs/how-to/use-wpclihandler-in-a-command/) | Integrate the handler into an existing command. |
| [WPCLIHandler reference](/docs/reference/wpclihandler/) | Constructor, level mapping, and formatter behaviour. |
| [Compatibility policy](/docs/explanation/compatibility-and-release-line-policy/) | Package and WordPress support windows. |
| [FAQs](/docs/faqs/) | Common questions about Monolog, WP-CLI, and this package. |

## Quick example

```php
<?php

use Monolog\Level;
use Monolog\Logger;
use MHCG\Monolog\Handler\WPCLIHandler;

$log = new Logger( 'mycommand' );
$log->pushHandler( new WPCLIHandler( Level::Info ) );

$log->info( 'Started running' );
$log->warning( 'Something happened of note' );
$log->error( 'An error has occurred' );
```
