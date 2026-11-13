# Hugo documentation site

Public user documentation lives in `website/content/`. See [AGENTS.md](../../../AGENTS.md) for site URL, theme, and deploy behaviour.

## Front matter

```yaml
---
title: Page title
weight: 10
---
```

Use `weight` to control sidebar order within a section.

## Audience and tone

Developers building WP-CLI commands who use Monolog for logging.

Use concise UK English. Use WordPress Coding Standards for PHP snippets.

Do not name documentation frameworks in page body text. Nav section labels (Tutorials, How-to, Reference, Explanation) are acceptable.

## Validation

From the repository root:

```shell
./scripts/invoke-hugo-site.sh build     # CI/production-parity build
./scripts/invoke-hugo-site.sh preview   # local browser preview (recommended)
./scripts/invoke-hugo-site.sh serve     # live dev server with hot reload
```

PowerShell: `scripts/Invoke-HugoSite.ps1` with the same commands.

Prefer `preview` or `serve` for local navigation checks; a plain `build` uses the production `baseURL` from `hugo.yaml`.
