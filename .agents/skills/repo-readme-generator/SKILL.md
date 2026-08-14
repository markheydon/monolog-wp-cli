---
name: repo-readme-generator
description: >-
  README generation for this repository. Builds a concise, Packagist-friendly
  README.md from repository-native PHP/Composer sources.
---

# README generator

Generate a concise, practical README.md for this repository by analysing repository-native sources of truth. Keep the result suitable for both GitHub and Packagist rendering. Do not invent details unsupported by files in this repository.

## Primary inputs

- `composer.json`
- `README.md`
- `src/**/*.php`
- `tests/**/*.php`
- `phpunit.xml.dist`
- `phpcs.xml.dist`
- `.github/workflows/*.yml`
- `.github/CONTRIBUTING.md`
- `LICENSE`

## Rules

1. Treat `composer.json` as the source of truth for package metadata.
2. Preserve strong repository-specific content unless repository files indicate change:
   - `composer require mhcg/monolog-wp-cli`
   - PHP and Monolog requirements from `composer.json`
   - Canonical usage examples unless code/tests show they are outdated
3. Link to the documentation site (`https://markheydon.me.uk/monolog-wp-cli/`) rather than raw markdown paths under `website/`.
4. Include only active, verifiable badges (Packagist version, PHP CI).
5. Use standard Markdown: headings, short paragraphs, lists, fenced code blocks.
6. Keep UK English and WordPress Coding Standards for PHP snippets.
7. This skill is for `README.md` only — not for `website/content/` pages.

## Sections to include when justified

- Project name and description
- Requirements
- Installation
- Usage (with real examples)
- Development commands
- Contributing (brief, link to CONTRIBUTING.md)
- Licence

Omit filler sections (architecture diagrams, technology stack) unless the repository supports them.
