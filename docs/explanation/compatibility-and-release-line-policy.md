# Explanation: Compatibility and Release-line Policy

This package currently follows the Monolog 2 release line and keeps runtime compatibility aligned with that choice.

## Why the package stays on Monolog 2 in the current line

Current package constraints are:

- PHP: `^7.2 || ^8.0`
- Monolog: `^2.5`

This allows projects on older but still-used PHP runtimes to integrate WP-CLI logging through Monolog without requiring a Monolog 3 migration.

## Why CI uses newer PHP for some jobs

CI separates concerns:

- Runtime compatibility jobs run across PHP 7.2 to 8.4.
- Unit tests run across PHP 7.2 to 8.4 with a compatible PHPUnit line per PHP version.
- Tooling jobs (audit, PHPMD, PHPCS) run on PHP 8.3.

This keeps runtime promises tied to package constraints while allowing modern tooling in dedicated jobs.

## Monolog 3 position

Monolog 3 support is treated as a future major-release path rather than a change in the current release line.

That separation avoids changing runtime expectations for existing users on the Monolog 2 line.