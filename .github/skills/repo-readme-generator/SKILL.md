---
name: repo-readme-generator
description: "README generation prompt for this repository. It builds a concise, Packagist-friendly README.md from repository-native PHP/Composer sources and preserves the library's canonical usage examples and active badges."
---

# README Generator Prompt

Generate a concise, practical README.md for this repository by analysing repository-native sources of truth. Keep the result suitable for both GitHub and Packagist rendering. Do not invent details that are not supported by files in this repository.

1. Use the repository's actual source files as the primary inputs.
   - `composer.json`
   - `README.md`
   - `src/**/*.php`
   - `tests/**/*.php`
   - `phpunit.xml.dist`
   - `phpcs.xml.dist`
   - `.editorconfig`
   - `.github/workflows/*.yml`
   - `.github/CONTRIBUTING.md`
   - `.github/CODE_OF_CONDUCT.md`
   - `LICENSE`

2. Treat `composer.json` as the source of truth for package metadata.
   - Package name, description, keywords, requirements, development dependencies, Composer scripts, licence, author, and support links should come from there unless a more specific repository file overrides presentation wording.

3. Preserve strong repository-specific content that already exists unless repository files indicate it should change.
   - Keep the `composer require mhcg/monolog-wp-cli` install command.
   - Keep the PHP version and Monolog requirement floor as detailed in `composer.json`.
   - Keep the README usage examples as canonical content unless code/tests show they are outdated.
   - Keep wording concise and practical, in UK English.

## Project Name and Description
- Extract the project name and purpose from `composer.json` and the existing README.
- Include a short description of what the handler does for Monolog and WP-CLI.

## Requirements
- State the supported PHP version and the Monolog requirement from `composer.json`.
- Mention WordPress/WP-CLI context only where the code or examples support it.

## Installation
- Include the Composer install command near the top.
- Keep this section short and easy to scan on Packagist.

## Development
- Summarise the local development commands from `composer.json`.
- Mention the active CI checks from `.github/workflows/php.yml` when useful.
- Mention coding standards and tools only if they are present in the repository.

## Usage
- Keep the current usage examples if they are still supported by the code and tests.
- Prefer one short introductory paragraph followed by fenced code blocks.
- Do not replace real examples with generic placeholders.
- For WordPress-oriented snippets, use WordPress Coding Standards style.

## Testing
- Mention PHPUnit, PHPMD, and PHPCS only if configured in the repository.
- Ground test or quality claims in `phpunit.xml.dist`, `phpcs.xml.dist`, Composer scripts, and workflow definitions.

## Contributing
- Summarise the contribution flow from `.github/CONTRIBUTING.md`.
- Link to the Code of Conduct if present.
- Keep this section brief.

## Licence
- State the licence from `LICENSE` and `composer.json`.

4. Omit sections that are not justified by this repository.
- Do not require sections such as `Technology Stack`, `Project Architecture`, `Project Structure`, `Key Features`, or any Spec Kit/governance sections unless the repository actually contains enough material to support them.
- Do not reference missing files such as `specs/*`, `.specify/*`, `AGENTS.md`, `tests/README.md`, `.github/copilot-instructions.md`.

5. Follow these output rules.
- Use standard Markdown only: headings, short paragraphs, flat lists, and fenced code blocks.
- Keep the opening compact so the README reads well on Packagist.
- Include only active, verifiable badges. For this repository, prefer the Packagist version badge and the PHP CI badge. Exclude Code Climate unless the repository clearly still uses it.
- Keep links working and aligned to the current `markheydon/monolog-wp-cli` repository location while preserving the Composer package name `mhcg/monolog-wp-cli`.
- Avoid filler sections, diagrams, or broad architecture text for this small library.

6. The goal is a README that helps two audiences quickly.
- Package users arriving via Packagist need a clear description, requirements, installation command, and examples.
- Contributors arriving via GitHub need development commands, testing expectations, and contribution links.