# Security maintenance (maintainers)

Operational guide for detecting and responding to security issues on `main` (Monolog 3) and `2.x` (Monolog 2). User-facing reporting policy remains in [SECURITY.md](../SECURITY.md).

Branching, Packagist tags, and when to ship a version: [dual-branch releases](dual-branch-releases.md).

## Scope

| Concern | `main` | `2.x` |
| --- | --- | --- |
| Runtime Composer dependencies | `monolog/monolog` `^3.0`, PHP `^8.1` | `monolog/monolog` `^2.5`, PHP `^7.2 \|\| ^8.0` |
| First-party code in this package | Same handler implementation (evaluate backports) | Same |
| Upstream Monolog security | Follow Monolog 3 advisories | Follow Monolog 2 advisories |

This package has a small runtime surface (logging via WP-CLI). Supply-chain monitoring and deliberate backports are the main levers.

## Detection signals

1. **PHP CI** — [`composer audit`](../.github/workflows/php.yml) on push and pull request (per branch; `2.x` workflows trigger only on `2.x`).
2. **Scheduled audit** — [`.github/workflows/security-audit.yml`](../.github/workflows/security-audit.yml) checks out `main` and `2.x` weekly (Mondays, aligned with Dependabot) and fails on locked advisories.
3. **Dependabot** — [`.github/dependabot.yml`](../.github/dependabot.yml) version updates on `main` and `target-branch: 2.x` for Composer and GitHub Actions.
4. **GitHub Security tab** — Dependabot alerts and dependency graph (repository settings below).
5. **Private vulnerability reports** — [GitHub advisory form](https://github.com/markheydon/monolog-wp-cli/security/advisories/new).

Local check before tagging:

```shell
composer run audit:locked
```

## Repository settings checklist

These cannot be defined in-repo; verify periodically in **Settings → Code security and analysis**:

- [ ] **Dependency graph** — enabled
- [ ] **Dependabot alerts** — enabled
- [ ] **Dependabot security updates** — enabled (opens fix PRs when alerts exist)
- [ ] **Private vulnerability reporting** — enabled (linked from SECURITY.md)

Last API check (2026-11-13): Dependabot security updates **enabled**; vulnerability alerts endpoint active; secret scanning **enabled** on the repository.

## Triage

When an advisory or audit failure appears:

1. Identify whether the vulnerable package is in **`require`** (runtime) or **`require-dev`** only.
2. **Runtime** — treat as release-blocking for the affected branch; bump and tag a patch after CI passes.
3. **Dev-only** — fix promptly on the affected branch; consumers are unaffected (their installs do not use this repo’s `require-dev` lockfile). A patch tag is optional—see [when a patch tag is required](dual-branch-releases.md#when-a-patch-tag-is-required).
4. Confirm the advisory applies to the **locked versions** on that branch (`composer audit --locked`).
5. **Scheduled Security audit** — each matrix leg is one ref (`main` or `2.x`). A failed `2.x` leg does not imply `^3.0` installs are vulnerable, and the reverse.

## Responding on `2.x`

1. Prefer merging a Dependabot pull request targeting `2.x` when one exists.
2. Otherwise update `composer.lock` on `2.x`, run `composer run qa` (and WordPress smoke if the change is risky).
3. **Patch tag** — required for runtime or `src/` fixes; for dev-only lockfile updates, fix the branch first and tag only if you want a published snapshot (note “no consumer action” in the release).

PHP CI for `2.x` is defined only on that branch (PHP 7.2–8.5 matrix). Do not merge `main`’s workflow file onto `2.x` without reviewing matrix differences.

## Backport from `main`

When a fix on `main` is security-related (label or release notes):

1. Decide if the same code paths exist on `2.x` (typically yes for `src/Monolog/`).
2. Cherry-pick or re-apply minimally on `2.x`.
3. Run `composer run qa` on `2.x` and ship a patch release if applicable.

## What we do not automate

- **CodeQL** — not in scope; first-party issues rely on reports and review.
- **Automatic backports** — human judgement per fix.

Release-line policy for consumers: [compatibility and release-line policy](../website/content/docs/explanation/compatibility-and-release-line-policy.md).
