---
name: support-governance
description: "Support governance workflow for this repository. Use when reviewing or updating supported PHP, Monolog, WordPress, WP-CLI, smoke-test tuples, CI matrices, or source-of-truth compatibility documentation. Central guidance for maintain-version-policy and check-support-test-alignment workflows."
---

# Support Governance

Use this skill as the shared operating guide for repository maintenance work related to version support policy and support-to-testing alignment.

## Purpose

Keep the repository's support statements, source-of-truth documents, smoke-test tuples, local scripts, and CI workflows aligned as upstream support windows move.

## Two workflow entry points

This skill supports two distinct workflows:

1. `maintain-version-policy`
   - Use when upstream WordPress or PHP support windows have changed, or when compatibility policy documentation needs refreshing.
   - Primary concern: source-of-truth policy and support statements.

2. `check-support-test-alignment`
   - Use when you need to verify that test scripts, Docker image tags, Composer scripts, and CI workflows still match documented policy.
   - Primary concern: executable coverage and implementation drift.

Keep those responsibilities separate. Do not let the policy workflow silently reshape CI, and do not let the alignment workflow invent new policy without updating the source-of-truth documents.

## Repository source files to inspect

Always inspect and reconcile these files before editing:

- `composer.json`
- `.github/workflows/php.yml`
- `README.md`
- `docs/README.md`
- `docs/explanation/compatibility-and-release-line-policy.md`
- `docs/how-to/test-under-wordpress.md`
- `docs-internal/php-version-strategy.md`
- `docs-internal/wordpress-support-policy.md`
- `docs-internal/readme-and-badges.md`
- `tests/wordpress/docker-compose.yml`
- `tests/wordpress/bin/setup-wordpress.sh`
- `tests/wordpress/bin/run-smoke.sh`

Inspect `src/` and `tests/` when behaviour claims or usage examples may need verification.

## Policy boundaries

Always preserve the distinction between these layers:

1. Package runtime compatibility
   - Governed primarily by the targeted Monolog major and `composer.json` constraints.

2. Official WordPress-runtime support
   - Governed by the repository's WordPress support policy.
   - Should be expressed as a maintained support window plus an explicit smoke-test tuple list.

3. Test coverage implementation
   - Governed by local scripts, Docker image availability, and CI workflows.

Do not collapse these layers into one generic "supported versions" statement.

## Current repository policy model

Unless repository source files have already changed, operate from this model:

- Monolog 2 line remains the active release line.
- Package runtime PHP compatibility remains aligned to that line in `composer.json`.
- Official WordPress-runtime support covers the current and previous WordPress major series.
- Eligible PHP branches for WordPress-runtime support are branches still in active support or security support upstream.
- Official WordPress-runtime support is maintained through an explicit tuple list, not an assumed Cartesian product.

## Upstream checks

When refreshing support policy or tuple coverage, verify these external facts before changing support statements:

- WordPress upstream support policy and current release line.
- PHP upstream supported branches and whether each branch is in active support or security support.
- Availability of official WordPress and WP-CLI Docker image tags for candidate tuples.

Do not broaden support claims based only on what seems plausible. Use what can be verified.

## Editing rules

- Prefer minimal, targeted edits.
- Keep README and public docs concise, user-facing, and in UK English.
- Keep maintainer rationale in `docs-internal/`.
- Keep workflow and prompt files concise; this skill is the detailed operating source.
- If policy changes require CI or script changes, make that dependency explicit.
- If CI or script drift is found, update the implementation or clearly report the mismatch.

## Workflow-specific outcomes

### For `maintain-version-policy`

Produce or update:

- source-of-truth policy documents
- public compatibility wording
- any snapshot dates or supported tuple lists tied to current policy

Report:

- which policy files changed
- which upstream facts were checked
- which support windows or tuples changed
- any follow-up work needed in tests or CI

### For `check-support-test-alignment`

Produce or update:

- Docker image tuple configuration
- local WordPress smoke scripts
- Composer script surface when required for tuple selection
- CI matrix entries and job wording

Report:

- which implementation files changed
- which documented tuples are now exercised
- any remaining gaps between policy and executable coverage

## Final verification checklist

- Do `README.md`, `docs/`, and `docs-internal/` describe the same support model?
- Does `.github/workflows/php.yml` exercise the tuples claimed as officially supported?
- Do local WordPress scripts accept the same tuple parameters that CI uses?
- Is package runtime compatibility still clearly separated from WordPress-runtime support?
- Are any snapshot dates and tuple lists current and internally consistent?