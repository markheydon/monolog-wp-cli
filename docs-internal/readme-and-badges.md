# README and Badge Policy

## Intent

Keep README.md concise, accurate, and suitable for both GitHub and Packagist display.

## Source of truth

Use repository-native files when updating README content:
- composer.json
- .github/workflows/php.yml
- phpunit.xml.dist
- phpcs.xml.dist
- src/ and tests/

## Badge policy

Include only active, verifiable badges.

Current policy:
- Keep Packagist version badge.
- Keep PHP CI badge.
- Do not include Code Climate badge (service not in active use).

## Content policy

- Preserve the install command for the current package identity.
- Preserve representative usage examples unless code/tests indicate they are outdated.
- Avoid adding sections that do not reflect real repository artefacts.
