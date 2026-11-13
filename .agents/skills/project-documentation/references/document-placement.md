# Document placement guide

Use this guide after choosing the Diátaxis document type in [diataxis.md](diataxis.md).

## Three channels

This repository separates documentation by audience and publish path:

| Channel | Path | Primary audience |
| --- | --- | --- |
| Repository front door | `README.md` | Evaluators, Packagist readers, quick start |
| Public documentation site | `website/content/` | Package users (WP-CLI and Monolog integrators) |
| Maintainer notes | `docs/` | Maintainers and coding agents |

Ground the choice in [AGENTS.md](../../../AGENTS.md) and existing section structure under `website/content/docs/`.

## Public site (`website/content/`)

- Hugo with Hextra; published to `https://markheydon.me.uk/monolog-wp-cli/` on GitHub Release.
- User docs use section folders: `tutorials/`, `how-to/`, `reference/`, `explanation/`, plus standalone pages such as `faqs.md`.
- Match Diátaxis type to section where possible.
- Do not copy maintainer-only policy from `docs/` without rewriting for package users.

See [hugo-site.md](hugo-site.md) for front matter and build commands.

## Put content in `README.md` when

- It explains what the package is, requirements, installation, and minimal usage.
- The primary audience is contributors, evaluators, or Packagist readers.
- The content must be visible on the repository front page and Packagist.

Follow [readme-packagist.md](readme-packagist.md).

## Put content in `docs/` when

- The audience is maintainers (release policy, smoke tests, badge rules, transfer notes).
- It documents operational or governance material that should not appear on the public Hugo site as-is.
- It supports agent workflows and need not be rewritten for end users.

## Decision rules

- Favour `README.md` for orientation, install, and links to the documentation site.
- Favour `website/content/` for substantive package-user tutorials, how-tos, reference, and explanations.
- Favour `docs/` for maintainer policy and historical context.
- Avoid duplicating the same guidance in README and the Hugo site; link instead.
- If a README section becomes long or reference-like, move detail into `website/content/` and leave a short summary plus link in the README.
