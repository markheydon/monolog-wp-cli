# Contributions welcome!

## Code changes

For fixes, improvements, and features, fork the repository and open a pull request. Reference the related issue where relevant.

## Documentation changes

User-facing documentation lives in `website/content/`. Maintainer notes live in `docs/`.

- Edit `website/content/` for tutorials, how-to guides, reference pages, FAQs, and other public docs.
- Edit `docs/` only for maintainer and policy notes.
- Preview the site locally with `./scripts/invoke-hugo-site.sh serve` (bash/zsh/WSL) or `.\scripts\Invoke-HugoSite.ps1 serve` (PowerShell). See [website/README.md](../website/README.md).

The published site is at <https://markheydon.me.uk/monolog-wp-cli/>.

## Dev Container and Codespaces

Open the repository in a Dev Container (VS Code / Cursor) or GitHub Codespaces. The configuration uses `mcr.microsoft.com/devcontainers/php:8.3` with Docker on the host for WordPress smoke tests and the Hugo docs build script.

After the container is created, `post-create` runs `composer install`, `composer validate --strict`, ensures `rsync` is available, and `composer run qa`. WordPress integration still uses nested containers:

```shell
composer run test:wp
```

The Hugo helper script (`./scripts/invoke-hugo-site.sh`) detects when bind mounts are not visible to the host Docker daemon (common with Docker-outside-of-Docker) and falls back to `docker cp` for `build` and `preview`. Use `preview` rather than `serve` for docs checks in Dev Containers and Codespaces unless Hugo Extended is installed locally in the container. Hugo preview defaults to port **1314** so it does not clash with WordPress smoke on **8080**; the script will step to the next free port if either is already bound.

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
