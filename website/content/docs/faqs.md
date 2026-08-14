---
title: FAQs
weight: 50
---

## What is Monolog?

Monolog is an open source PHP library that sends logs to files, sockets, inboxes, databases, and various web services. See the [Monolog homepage](https://seldaek.github.io/monolog/) for full details.

## What is this project?

This project is a Monolog handler that lets you send log messages to [WP-CLI](https://wp-cli.org/) when running custom `wp` commands. Use it when building WP-CLI commands that log through Monolog.

## Why use Monolog instead of WP_CLI::log()?

Monolog supports multiple handlers at once. For example, a data-import command can write detailed logs to a file, send alerts to Slack, and show warnings and errors on the command line in the same run.

## How does WPCLIHandler work with --quiet and --debug?

WP-CLI has built-in `--debug` and `--quiet` behaviour, and WPCLIHandler respects it.

- `wp my-command --quiet` — only Monolog `ERROR` level and above appear on the command line.
- `wp my-command --debug` — `DEBUG` level messages appear along with WP-CLI debug output.

## How do I include context and extra data in output?

By default, command-line output is message-only. To include Monolog `context` and `extra` data, either:

- pass `true` as the third (`$verbose`) argument to the `WPCLIHandler` constructor, or
- set `WP_DEBUG` to true in `wp-config.php`.

See the [WPCLIHandler reference](/docs/reference/wpclihandler/) for formatter details.
