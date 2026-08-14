# Documentation site

User-facing documentation for [mhcg/monolog-wp-cli](https://packagist.org/packages/mhcg/monolog-wp-cli), built with [Hugo](https://gohugo.io/) and the [Hextra](https://github.com/imfing/hextra) theme.

Published at [markheydon.me.uk/monolog-wp-cli](https://markheydon.me.uk/monolog-wp-cli/).

## Prerequisites

- Docker or Podman (for containerised builds), or Hugo Extended installed locally.
- Go (for Hugo module theme resolution on first build).

## Local preview

From the repository root:

```powershell
.\scripts\Invoke-HugoSite.ps1 serve
```

Other commands:

```powershell
.\scripts\Invoke-HugoSite.ps1 build    # output to website/public/
.\scripts\Invoke-HugoSite.ps1 preview  # static build + nginx on port 8080
```

With a local Hugo Extended install:

```shell
cd website
hugo server -D
```

## GitHub Pages

The site deploys via GitHub Actions when a GitHub Release is published. Use **workflow_dispatch** on the Hugo workflow for a one-off deploy without a release.

Repository Settings → Pages should use **GitHub Actions** as the source.
