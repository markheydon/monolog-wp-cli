---
name: support-governance
description: >-
  Support governance for this repository. Use when reviewing or updating
  supported PHP, Monolog, WordPress, WP-CLI, smoke-test tuples, CI matrices, or
  source-of-truth compatibility documentation.
---

# Support governance

Shared operating guide for version-support policy and support-to-testing alignment. Repository-wide agent context and change discipline: [AGENTS.md](../../../AGENTS.md).

## Purpose

Keep support statements, source-of-truth documents, smoke-test tuples, local scripts, and CI workflows aligned as upstream support windows move.

## Two workflow entry points

1. **Maintain version policy**
   - Upstream WordPress or PHP support windows changed, or policy docs need refreshing.
   - Primary concern: source-of-truth policy and support statements.

2. **Check support/test alignment**
   - Verify test scripts, Docker image tags, Composer scripts, and CI match documented policy.
   - Primary concern: executable coverage and implementation drift.

Keep those responsibilities separate.

## Repository source files to inspect

- `composer.json`
- `.github/workflows/php.yml`
- `README.md`
- `website/content/docs/explanation/compatibility-and-release-line-policy.md`
- `docs/test-under-wordpress.md`
- `docs/php-version-strategy.md`
- `docs/wordpress-support-policy.md`
- `docs/readme-and-badges.md`
- `tests/wordpress/docker-compose.yml`
- `tests/wordpress/bin/setup-wordpress.sh`
- `tests/wordpress/bin/run-smoke.sh`

Inspect `src/` and `tests/` when behaviour claims may need verification.

## Policy boundaries

1. **Package runtime compatibility** — governed by Monolog major and `composer.json`.
2. **Official WordPress-runtime support** — maintained window plus explicit smoke-test tuple list.
3. **Test coverage implementation** — local scripts, Docker images, CI workflows.

Do not collapse these into one generic "supported versions" statement.

## Current policy model

Unless repository files have changed:

- `main` targets Monolog 3 (`^3.0`) on PHP `^8.1`.
- v2.x branch maintained separately for Monolog 2.
- WordPress-runtime support covers current and previous WordPress major series.
- Support expressed as explicit tuple list, not assumed Cartesian product.

## Editing rules

- Prefer minimal, targeted edits.
- Keep README and `website/content/` concise and user-facing.
- Keep maintainer rationale in `docs/`.
- If policy changes require CI or script changes, make that dependency explicit.

## Final verification checklist

- Do `README.md`, `website/content/`, and `docs/` describe the same support model?
- Does `.github/workflows/php.yml` exercise the tuples claimed as officially supported?
- Do local WordPress scripts accept the same tuple parameters as CI?
- Is package runtime compatibility clearly separated from WordPress-runtime support?
