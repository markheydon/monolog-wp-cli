# Copilot Instructions for monolog-wp-cli

## Scope and Audience

- This repository is a PHP library for Monolog + WP-CLI integration.
- Prefer practical, developer-facing output over marketing copy.
- Use concise UK English.

## Repository Source of Truth

When writing or updating code or documentation, prioritise these files:

- `composer.json` for package metadata, requirements, scripts, and support links.
- `src/` and `tests/` for behaviour and usage evidence.
- `.github/workflows/php.yml` for CI and compatibility statements.
- `docs-internal/readme-and-badges.md` for README and badge policy.
- `docs-internal/package-identity.md` for package naming policy.
- `docs-internal/php-version-strategy.md` for PHP/Monolog compatibility policy.
- `docs-internal/wordpress-support-policy.md` for WordPress-runtime support policy and smoke-test tuples.

Do not invent behaviour, compatibility claims, or roadmap commitments that are not supported by repository files.

## Current Policy Guardrails

- Preserve Composer package identity `mhcg/monolog-wp-cli` unless explicitly asked to perform a migration.
- Keep runtime compatibility statements aligned with `composer.json` and CI evidence.
- Respect the current line policy: Monolog 2 line (`^2.5`) with runtime PHP floor aligned to that line (currently represented in `composer.json` as `^7.2 || ^8.0`).
- Treat Monolog 3 support as a future major-release path unless repository files explicitly change this status.

## Documentation Rules

- Keep `README.md` suitable for both GitHub and Packagist.
- Keep only active, verifiable badges.
- Preserve representative usage examples unless code/tests show they are outdated.
- Keep public docs in `docs/` user-facing and technical.
- Keep maintainer-only rationale in `docs-internal/`; do not copy internal-only detail directly into public docs unless rewritten for end users.
- Apply Diataxis to public docs in `docs/`:
	- Tutorials: learning-oriented, step-by-step guidance.
	- How-to: task-oriented instructions for known goals.
	- Reference: factual behaviour, options, constraints, and mappings.
	- Explanation: rationale, trade-offs, and policy context.
- Keep each public doc page clearly anchored to one primary Diataxis type.
- Use `.github/skills/documentation-writer/SKILL.md` as the primary Diataxis guidance for content in `docs/`.
- Use `.github/skills/repo-readme-generator/SKILL.md` for `README.md` updates only; do not treat it as a generator for `docs/` pages.
- Keep package runtime compatibility wording separate from WordPress-runtime support wording.
- For WordPress-facing code snippets in `README.md` and `docs/`, use WordPress Coding Standards style (for example spacing, control-structure formatting, and WordPress naming conventions where relevant).

## Support Governance Workflows

- Use `.github/skills/support-governance/SKILL.md` as the shared operating guide when maintaining version-support policy or checking support-to-testing alignment.
- Use the `maintain-version-policy` workflow when upstream WordPress or PHP support windows have moved, or when policy/source-of-truth docs need refreshing.
- Use the `check-support-test-alignment` workflow when test scripts, Docker tuples, Composer scripts, CI jobs, or documentation may have drifted away from the documented policy.
- Keep the workflow files thin and let the shared skill hold the detailed operating rules.

## Change Discipline

- Prefer minimal, targeted edits.
- Avoid unrelated refactors while doing documentation updates.
- If a statement cannot be verified from repository-native files, either omit it or mark it explicitly as planned and unimplemented only when the repository already says so.
