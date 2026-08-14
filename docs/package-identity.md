# Package Identity and Naming

## Canonical package name

The Composer package name is:

- mhcg/monolog-wp-cli

This value is the package identity used by consumers and should remain stable unless a deliberate migration is planned.

## Why this matters

- Composer users install with `composer require mhcg/monolog-wp-cli`.
- Existing downstream projects depend on this name.
- Renaming the package is a breaking ecosystem change and must be treated as a separate migration project.

## Related identifiers

- Repository owner/name: markheydon/monolog-wp-cli
- PHP namespace: MHCG\\Monolog\\

These identifiers can differ from the Composer package vendor and still be valid.

## Policy

- Do not change `name` in composer.json as part of routine maintenance.
- If a rename is desired, plan and communicate migration steps first.
