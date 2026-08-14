---
title: Compatibility and release-line policy
weight: 10
---

This package targets the Monolog 3 release line. Package requirements and WordPress-runtime support are described separately because they answer different questions.

## Package requirements

Current Composer constraints:

- PHP: `^8.1`
- Monolog: `^3.0`

If you still use Monolog 2, use the maintained v2.x branch instead of `main`.

## WordPress-runtime support

WordPress upstream only officially supports the latest major release. This package maintains a practical support window for WP-CLI users:

- the current and previous WordPress major release series
- only WordPress/PHP combinations that are part of an explicit, tested tuple list

That avoids claiming support for every possible WordPress and PHP combination when only a subset is verified.

## Currently tested WordPress/PHP combinations

As of 2026-07-13, the package is smoke-tested against:

- WordPress `7.0` on PHP `8.4`
- WordPress `7.0` on PHP `8.5`
- WordPress `6.8` on PHP `8.3`
- WordPress `6.8` on PHP `8.4`

These tuples are chosen because they satisfy the support policy and have corresponding official WordPress and WP-CLI images available. The list is updated deliberately when upstream support or image availability changes.

## Monolog 2 legacy support

The v2.x branch uses PHP `^7.2 || ^8.0` with Monolog `^2.5`. Critical fixes may be backported when warranted. New feature development focuses on the Monolog 3 line on `main`.
