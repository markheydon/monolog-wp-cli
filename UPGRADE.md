# Upgrade Guide: Monolog 2 to Monolog 3

This guide covers upgrading from monolog-wp-cli v2.x (Monolog 2 support) to v3.x (Monolog 3 support).

## Overview

v3.0.0 drops support for PHP 7.2–8.0 and Monolog 2. The minimum supported versions are now:

- **PHP 8.1+**
- **Monolog 3.0+**

The upgrade is primarily about Composer constraints and adapting to Monolog 3's API changes (the `Level` enum and `LogRecord` object).

## Step 1: Update Composer constraints

In your `composer.json`, update the monolog-wp-cli requirement:

```json
{
  "require": {
    "mhcg/monolog-wp-cli": "^3.0",
    "monolog/monolog": "^3.0"
  }
}
```

If you are on PHP 7.2–8.0, you will need to upgrade your PHP runtime first before proceeding.

Then run:

```shell
composer update
```

## Step 2: Adapt to Monolog 3 API changes

### Logger level constants → Level enum

Monolog 3 replaces integer level constants with the `Level` enum. If you instantiate `WPCLIHandler` directly or reference Monolog levels in your code, update your imports and usage:

**Before (Monolog 2):**

```php
use Monolog\Logger;

$log->pushHandler(new WPCLIHandler(Logger::WARNING));
```

**After (Monolog 3):**

```php
use Monolog\Level;

$log->pushHandler(new WPCLIHandler(Level::Warning));
```

Enum case names are capitalized: `Level::Debug`, `Level::Info`, `Level::Notice`, `Level::Warning`, `Level::Error`, `Level::Critical`, `Level::Alert`, `Level::Emergency`.

### LogRecord object vs array

Monolog 3 replaces record arrays with the `LogRecord` object. The `WPCLIHandler` has been updated to accept `LogRecord`, so most code will continue to work without changes.

If you have custom handler code that creates records manually or accesses record fields directly, refer to the [Monolog 3 upgrade guide](https://github.com/Seldaek/monolog/blob/main/UPGRADE.md) for detailed migration steps.

### Custom handler map override

If you are passing a custom logger map override to `WPCLIHandler`, update level constants to enum cases:

**Before:**

```php
use Monolog\Logger;

$customMap = [
    Logger::NOTICE => [
        'method' => 'warning',
        'includeLevelName' => true,
    ],
];
```

**After:**

```php
use Monolog\Level;

$customMap = [
    Level::Notice->value => [
        'method' => 'warning',
        'includeLevelName' => true,
    ],
];
```

## Step 3: Test your changes

Run your existing unit tests to ensure logging still works as expected:

```shell
./vendor/bin/phpunit
```

If you run WP-CLI commands, verify logging output behaves correctly in your local environment.

## Staying on Monolog 2

If you need to remain on Monolog 2, the v2.x branch is maintained separately:

```json
{
  "require": {
    "mhcg/monolog-wp-cli": "^2.3",
    "monolog/monolog": "^2.5"
  }
}
```

The v2.x branch receives critical security fixes. Feature development focuses on v3.x.

## Further reading

- [Monolog 3 upgrade guide](https://github.com/Seldaek/monolog/blob/main/UPGRADE.md)
- [Monolog 3 documentation](https://seldaek.github.io/monolog/)
