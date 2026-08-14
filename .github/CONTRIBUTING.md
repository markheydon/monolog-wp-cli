# Contributions welcome!

## Code changes

For fixes, improvements, and features, fork the repository and open a pull request. Reference the related issue where relevant.

## Documentation changes

User-facing documentation lives in `website/content/`. Maintainer notes live in `docs/`.

- Edit `website/content/` for tutorials, how-to guides, reference pages, FAQs, and other public docs.
- Edit `docs/` only for maintainer and policy notes.
- Preview the site locally with `./scripts/invoke-hugo-site.sh serve` (bash/zsh/WSL) or `.\scripts\Invoke-HugoSite.ps1 serve` (PowerShell). See [website/README.md](../website/README.md).

The published site is at <https://markheydon.me.uk/monolog-wp-cli/>.

## Development checks

```shell
composer install
composer run qa
```

For documentation site changes:

```shell
./scripts/invoke-hugo-site.sh build
```

Please follow the [Code of Conduct](CODE_OF_CONDUCT.md).
