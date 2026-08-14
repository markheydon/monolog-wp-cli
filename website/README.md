# Documentation site

User-facing documentation for [mhcg/monolog-wp-cli](https://packagist.org/packages/mhcg/monolog-wp-cli), built with [Hugo](https://gohugo.io/) and the [Hextra](https://github.com/imfing/hextra) theme.

Published at [markheydon.me.uk/monolog-wp-cli](https://markheydon.me.uk/monolog-wp-cli/).

## Prerequisites

- Docker or Podman (for containerised builds), or Hugo Extended installed locally.
- Go (for Hugo module theme resolution on first build).

## Local preview

From the repository root, use either helper script (both wrap the same containerised Hugo commands):

**Bash / zsh / WSL** (no PowerShell required):

```shell
./scripts/invoke-hugo-site.sh serve
./scripts/invoke-hugo-site.sh preview   # static build + nginx on port 8080
./scripts/invoke-hugo-site.sh build     # production-parity output to website/public/
```

**PowerShell** (Windows):

```powershell
.\scripts\Invoke-HugoSite.ps1 serve
.\scripts\Invoke-HugoSite.ps1 preview
.\scripts\Invoke-HugoSite.ps1 build
```

Use `serve` or `preview` for browser checks. A plain `build` followed by serving `website/public/` at the site root will break CSS and navigation links because asset paths include the `/monolog-wp-cli/` prefix from `hugo.yaml`.

With a local Hugo Extended install:

```shell
cd website
hugo server -D
```

## GitHub Pages

The site deploys via GitHub Actions when a GitHub Release is published. Use **workflow_dispatch** on the Hugo workflow for a one-off deploy without a release.

Repository Settings → Pages should use **GitHub Actions** as the source.
