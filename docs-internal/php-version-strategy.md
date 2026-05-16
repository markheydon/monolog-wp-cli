# PHP Version Strategy

## Intent

Set PHP support based on the Monolog major version this package targets.

This package is an add-on for Monolog, so its PHP compatibility should not become more restrictive than the Monolog line it is built for unless there is a clear technical reason.

## Core policy

- Treat Monolog compatibility as the primary driver for PHP support.
- Do not raise the minimum PHP version only because the maintainer environment, CI default, or personal preference has moved on.
- If this package needs a higher PHP minimum than the targeted Monolog major, treat that as an explicit breaking decision and document why.

## Version-line strategy

### Line targeting Monolog 2

- Dependency target: `monolog/monolog:^2.5`
- PHP policy: align with Monolog 2.5 support, which is PHP 7.2+
- Result: the package line built for Monolog 2 should remain installable on PHP 7.2+ unless the code genuinely requires something newer

### Line targeting Monolog 3

- Dependency target: `monolog/monolog:^3.0`
- PHP policy: align with Monolog 3 support, which is PHP 8.1+
- Result: the Monolog 3 upgrade should ship as a separate breaking package line with PHP 8.1+ support

## Release policy

- Keep the Monolog 2-compatible package line available for users who still need that ecosystem.
- Introduce Monolog 3 support in the next breaking release rather than forcing both Monolog and PHP upgrades into the current line.
- Only drop an older PHP version when the targeted Monolog line also drops it, or when maintaining support becomes technically unrealistic and that break is clearly documented.

## Practical implications

- The `require.php` constraint in `composer.json` should represent consumer runtime compatibility, not the PHP version used by the dev container.
- CI may run on newer PHP versions for convenience, but that must not be mistaken for the supported minimum.
- Where practical, CI should include the lowest supported PHP version for the active Monolog line plus the latest supported stable PHP version.
- Development tooling may need different versions per package line; tool limitations are not, by themselves, a reason to raise the runtime PHP floor without a release-policy decision.

## Current recommendation

- For the current Monolog 2-based codebase, the package should follow the Monolog 2.5 PHP floor rather than enforcing PHP 8.3+.
- When the package is upgraded to Monolog 3, that release line should move to PHP 8.1+ at the same time.
- If the repository keeps a PHP 8.3 dev container or modern-only QA tooling, treat that as a maintainer workflow choice, not the consumer support policy.
