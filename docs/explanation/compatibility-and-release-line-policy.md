# Explanation: Compatibility and Release-line Policy

This package currently follows the Monolog 2 release line and separates package runtime compatibility from official WordPress-runtime support.

## Package runtime compatibility

Current package constraints are:

- PHP: `^7.2 || ^8.0`
- Monolog: `^2.5`

This is the package runtime floor for the current release line. It exists so projects on older but still-used PHP runtimes can integrate WP-CLI logging through Monolog without requiring a Monolog 3 migration.

Package runtime compatibility is primarily governed by the targeted Monolog major, not by the newest PHP version used in maintainer tooling or WordPress smoke tests.

## WordPress-runtime support is a separate policy layer

WordPress upstream only officially supports the latest major release. For this package, that upstream policy is too narrow to be the only maintenance rule because downstream users still need a practical compatibility window and real WP-CLI smoke coverage.

This repository therefore treats WordPress-runtime support as a maintained downstream window with two rules:

- support the current and previous WordPress major release series
- only describe WordPress/PHP combinations as officially supported when they are part of the repository's explicit smoke-test tuple list

That avoids two common mistakes:

- assuming that package Composer constraints automatically prove WordPress-runtime support
- implying support for every possible WordPress/PHP cross-product when only a subset is actually tested

## Current WordPress-runtime support snapshot

As of 2026-05-30, the repository's official WordPress smoke coverage is maintained as explicit tuples:

- WordPress `7.0` on PHP `8.4`
- WordPress `7.0` on PHP `8.5`
- WordPress `6.8` on PHP `8.3`
- WordPress `6.8` on PHP `8.4`

These tuples were chosen because they satisfy the repository policy and have corresponding official WordPress and WP-CLI images available.

This tuple list is intentionally narrower than a full Cartesian product. If upstream image availability or support windows change, the tuple list should be updated deliberately and the public docs should move with it.

## Why CI uses different PHP ranges for different jobs

CI separates concerns:

- Runtime compatibility jobs run across PHP 7.2 to 8.5.
- Unit tests run across PHP 7.2 to 8.5 with a compatible PHPUnit line per PHP version.
- Tooling jobs run on PHP 8.3.
- WordPress smoke jobs run only for the explicit WordPress/PHP tuples that the repository currently treats as officially supported.

This keeps package runtime promises tied to `composer.json` while giving WordPress-runtime support its own concrete, executable test surface.

## Monolog 3 position

Monolog 3 support is treated as a future major-release path rather than a change in the current release line.

That separation avoids changing runtime expectations for existing users on the Monolog 2 line.