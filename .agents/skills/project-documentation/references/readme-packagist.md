# README and Packagist

Generate or update `README.md` from repository-native sources. Keep the result suitable for both GitHub and Packagist.

Policy detail: [docs/readme-and-badges.md](../../../docs/readme-and-badges.md). Repository-wide conventions: [AGENTS.md](../../../AGENTS.md).

## Primary inputs

- `composer.json`
- `README.md` (existing)
- `src/**/*.php`, `tests/**/*.php`
- `phpunit.xml.dist`, `phpcs.xml.dist`
- `.github/workflows/*.yml`, `.github/CONTRIBUTING.md`
- `LICENSE`

## Rules

1. Treat `composer.json` as the source of truth for package metadata.
2. Preserve strong repository-specific content unless files indicate change:
   - `composer require mhcg/monolog-wp-cli`
   - PHP and Monolog requirements from `composer.json`
   - Canonical usage examples unless code or tests show they are outdated
3. Link to the documentation site (`https://markheydon.me.uk/monolog-wp-cli/`) rather than raw paths under `website/`.
4. Include only active, verifiable badges (Packagist version, PHP CI per badge policy).
5. Use standard Markdown; UK English; WordPress Coding Standards for PHP snippets.
6. Do not name documentation frameworks in README prose.

## Sections to include when justified

- Project name and description
- Requirements
- Installation
- Usage (with real examples)
- Development commands
- Contributing (brief, link to CONTRIBUTING.md)
- Licence

Omit filler sections unless the repository supports them.
