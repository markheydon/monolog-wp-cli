# AGENTS.md

Guidelines for coding agents working in this repository.

## Project context

**monolog-wp-cli** (`mhcg/monolog-wp-cli`) is a PHP library that extends Monolog with a handler routing log output through WP-CLI when running `wp` commands.

| Directory | Purpose |
|-----------|---------|
| `src/` | Library source |
| `tests/` | PHPUnit, runtime smoke, WordPress integration |
| `website/` | User-facing documentation site (Hugo / GitHub Pages) |
| `docs/` | Maintainer-only operational notes and policies |

## PHP conventions

- Follow PSR-12 for `src/` and `tests/` (enforced by PHPCS).
- Preserve Composer package identity `mhcg/monolog-wp-cli` unless explicitly asked to migrate.
- Keep runtime compatibility statements aligned with `composer.json` and CI evidence.
- Current `main` targets Monolog 3 (`^3.0`) on PHP `^8.1`. The v2.x branch is maintained separately for Monolog 2.

## Documentation conventions

- Keep `README.md` suitable for both GitHub and Packagist.
- Public user docs live in `website/content/`.
- Maintainer notes live in `docs/`.
- Use concise UK English.
- Do not mention Diataxis or other documentation frameworks in user-facing site copy.
- For WordPress-oriented snippets, use WordPress Coding Standards style.

## Repository source of truth

When writing or updating code or documentation, prioritise:

- `composer.json` for package metadata, requirements, scripts, and support links
- `src/` and `tests/` for behaviour and usage evidence
- `.github/workflows/php.yml` for CI and compatibility statements
- `docs/readme-and-badges.md` for README and badge policy
- `docs/package-identity.md` for package naming policy
- `docs/php-version-strategy.md` for PHP/Monolog compatibility policy
- `docs/wordpress-support-policy.md` for WordPress-runtime support policy and smoke-test tuples

Do not invent behaviour, compatibility claims, or roadmap commitments unsupported by repository files.

## Testing

Before submitting changes, validate as described in [CONTRIBUTING.md](.github/CONTRIBUTING.md):

```shell
composer run qa
composer run test:wp
```

For documentation site changes:

```powershell
# CI/production-parity build (uses hugo.yaml baseURL — not for local browser preview)
.\scripts\Invoke-HugoSite.ps1 build

# Local browser preview with correct asset paths (recommended for visual checks)
.\scripts\Invoke-HugoSite.ps1 preview -Runtime docker

# Live dev server with hot reload
.\scripts\Invoke-HugoSite.ps1 serve -Runtime docker
```

## Project skills

Use these skills for specialised workflows:

| Skill | When to use |
|-------|-------------|
| `documentation-writer` | Creating or editing `website/content/`, user-facing docs |
| `repo-readme-generator` | Updating `README.md` only |
| `support-governance` | Version policy, smoke-test tuples, CI alignment |

Skills live in `.agents/skills/<skill-name>/SKILL.md`.

### Support governance workflows

- **Maintain version policy** — upstream WordPress or PHP support windows changed, or policy docs need refreshing.
- **Check support/test alignment** — verify Docker tuples, Composer scripts, and CI match documented policy.

Use `.agents/skills/support-governance/SKILL.md` as the shared operating guide for both workflows.

## Documentation site (`website/`)

- Hugo with the [Hextra](https://github.com/imfing/hextra) theme via Go modules (`website/go.mod`).
- Hugo **extended** is required. Run commands from `website/` or use `scripts/Invoke-HugoSite.ps1`.
- The site deploys to GitHub Pages when a GitHub Release is published (`.github/workflows/hugo.yml`).
- Public URL: `https://markheydon.me.uk/monolog-wp-cli/`

## Change discipline

- Prefer minimal, targeted edits.
- Avoid unrelated refactors during documentation updates.
- If a statement cannot be verified from repository-native files, omit it or mark it as planned only when the repository already says so.

## Cursor Cloud specific instructions

This repository is a PHP library (`mhcg/monolog-wp-cli`): a Monolog handler that
routes log output through WP-CLI. There is no long-running application service —
"running it" means exercising the handler via the unit tests, the runtime smoke
script, and the WordPress/WP-CLI smoke workflow.

The commands below are the source of truth in `composer.json` (`scripts`),
`README.md` (`## Development`), and `.github/workflows/php.yml`. Prefer those.
The update script already installs PHP 8.3, Composer, Docker, and runs
`composer install`, so dependencies are ready when a session starts.

### Core checks (no Docker required)

- `composer run test` — PHPUnit unit suite (`tests/`).
- `composer run test:runtime-smoke` — standalone script that mocks `WP_CLI` and
  asserts level routing.
- `composer run lint` — PHPMD (`src/`) + PHPCS (PSR-12 across `src/` and `tests/`).
- `composer run qa` — runs `test` + `lint`.

### WordPress smoke workflow (needs Docker)

The `test:wp*` and `wp:env:*` scripts use `docker compose` with `tests/wordpress/docker-compose.yml`
(MariaDB + WordPress + WP-CLI). Non-obvious gotchas:

- Docker is NOT auto-started on session boot. Start the daemon first, e.g.
  `sudo dockerd` (running it in a background/tmux session works well). The daemon
  is configured for `fuse-overlayfs` with the containerd snapshotter disabled in
  `/etc/docker/daemon.json` — this is required for Docker 29 to work in this VM.
- The `ubuntu` user is in the `docker` group, but a shell started before the
  daemon/group existed may not have it applied. If `docker ps` gives a permission
  error, prefix commands with `sg docker -c '...'` (a fresh login shell picks the
  group up automatically).
- `test:wp:setup` requires `rsync` (installed by the update script) and runs
  `composer update` inside `tests/wordpress/fixtures/monolog-wp-cli-smoke`, which
  mirrors the local package into the WordPress container as a plugin.
- Full flow: `composer run wp:env:up`, `composer run test:wp:setup`,
  `composer run test:wp:smoke`, then `composer run wp:env:down` to tear down.
  `composer run test:wp` chains the first three but does NOT tear down.
- Override the tuple with `WORDPRESS_VERSION` / `WORDPRESS_PHP_VERSION` env vars
  (defaults: WordPress 7.0 on PHP 8.4).

### Manual "hello world"

Route a Monolog log through a real WP-CLI command (after `wp:env:up` + `test:wp:setup`):

```
cd tests/wordpress
docker compose -f docker-compose.yml run --rm --entrypoint wp wpcli \
  monolog-smoke --level=notice --message="hello world"
# -> (NOTICE) hello world
```
