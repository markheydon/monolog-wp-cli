# WordPress Runtime Support Policy

## Executive summary

- Treat WordPress-runtime support as a maintained compatibility window, not as a claim that every Composer-installable environment is officially supported.
- Support the current and previous WordPress major release series.
- Limit official WordPress-runtime support to PHP branches that are still in active support or security support upstream.
- Maintain an explicit list of supported WordPress/PHP smoke-test tuples instead of assuming a full WordPress/PHP cross-product.

## Purpose

Define a repeatable policy for deciding which WordPress and PHP combinations this repository treats as officially supported in real WP-CLI runtime testing.

This policy complements, but does not replace, the package runtime policy in `docs-internal/php-version-strategy.md`.

## Why this is separate from package runtime compatibility

- Package runtime compatibility is primarily constrained by the targeted Monolog major and the package's Composer requirements.
- WordPress has its own release and support policy and only officially supports the latest major release upstream.
- Official WordPress Docker images do not guarantee that every desired WordPress/PHP combination exists at the same time.
- A downstream package therefore needs an explicit support window and explicit tested tuples.

## Policy rules

Apply these rules in order.

1. Define the WordPress support window as the current and previous WordPress major release series.
2. Define the eligible PHP window as branches that are still in active support or security support upstream.
3. Build and maintain an explicit tuple list of WordPress/PHP combinations that this repository tests under WP-CLI.
4. Only describe WordPress-runtime combinations as officially supported when they are both:
   - within the policy windows above, and
   - exercised by the repository's WordPress smoke workflow.
5. Do not imply support for the entire Cartesian product of supported WordPress majors and supported PHP branches unless the CI matrix actually covers that full product.
6. If an upstream image tag needed for a plausible tuple does not exist, document the chosen tuple coverage explicitly rather than silently broadening or narrowing policy language.

## Current application snapshot (2026-05-30)

- WordPress support window: current and previous major series.
- Runtime policy interpretation for this snapshot: WordPress `7.0.x` and `6.8.x` are the maintained series for official smoke coverage.
- Eligible PHP window for WordPress-runtime support: `8.3`, `8.4`, and `8.5`, because those branches are still upstream-supported on the snapshot date.

### Supported smoke-test tuples

- WordPress `7.0` on PHP `8.4`
- WordPress `7.0` on PHP `8.5`
- WordPress `6.8` on PHP `8.3`
- WordPress `6.8` on PHP `8.4`

These tuples are chosen because they are within the policy window and have corresponding official WordPress and WP-CLI images available.

## Maintenance rules

- Review this policy whenever a new WordPress major release is published.
- Review this policy whenever PHP support windows change upstream.
- Refresh the tuple list before changing README or public compatibility claims.
- Keep `README.md`, `docs/explanation/compatibility-and-release-line-policy.md`, `docs/how-to/test-under-wordpress.md`, and `.github/workflows/php.yml` aligned with the current tuple list.
- If the tuple list changes, update both the default local smoke target and the CI matrix deliberately; do not allow one to drift from the other.

## Decision checklist

- What are the current and previous WordPress major series today?
- Which PHP branches are still upstream-supported today?
- Which official WordPress and WP-CLI image tags exist for candidate tuples?
- Which tuples are exercised locally by default?
- Which tuples are exercised in CI?
- Do public docs describe only the tuples and support windows that the repository actually tests?