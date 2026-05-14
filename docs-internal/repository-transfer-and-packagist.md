# Repository Transfer and Packagist Behaviour

## Current state

- Repository moved from mhcg/monolog-wp-cli to markheydon/monolog-wp-cli.
- Package remains mhcg/monolog-wp-cli on Packagist.
- Packagist package is auto-updated and linked to the current GitHub repository.

## Practical outcome

Repository transfer does not require a package rename. Existing installs continue to work as long as the Packagist package remains available and metadata stays accurate.

## Required metadata hygiene

Keep composer.json support URLs aligned to the current repository location:
- support.issues -> https://github.com/markheydon/monolog-wp-cli/issues
- support.wiki -> https://github.com/markheydon/monolog-wp-cli/wiki
- support.source -> https://github.com/markheydon/monolog-wp-cli

## Notes for maintainers

- GitHub repository transfer provides redirects, but redirects should not be relied upon forever.
- If old owner/name is reused later, redirects can be removed by GitHub.
- Prefer updating canonical links proactively.
