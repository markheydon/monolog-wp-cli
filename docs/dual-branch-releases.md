# Dual-branch releases and Packagist (maintainers)

Operational playbook for the Monolog 3 line (`main`) and the Monolog 2 maintenance line (`2.x`). Use this with [security maintenance](security-maintenance.md) when auditing, tagging, or backporting.

User-facing constraints and upgrade paths: [compatibility and release-line policy](../website/content/docs/explanation/compatibility-and-release-line-policy.md) and [UPGRADE.md](../UPGRADE.md).

## Release lines

| Git branch | Monolog (runtime) | PHP (`require`) | Semver tags | Consumer constraint (typical) |
| --- | --- | --- | --- | --- |
| `main` | `^3.0` | `^8.1` | `v3.*.*` | `mhcg/monolog-wp-cli`: `^3.0` |
| `2.x` | `^2.5` | `^7.2 \|\| ^8.0` | `v2.*.*` | `mhcg/monolog-wp-cli`: `^2.3` |

- Feature development targets `main`. `2.x` receives critical fixes and dependency hygiene when warranted.
- Check out the branch that matches the release line before changing `composer.json`, workflows, or tagging.

## Packagist (one package, many tags)

- Composer package name: **`mhcg/monolog-wp-cli`** (see [package identity](package-identity.md)).
- There is **one** Packagist project. Tags from **both** branches are published as versions on that package.
- Packagist auto-updates from this GitHub repository when tags are pushed (see [repository transfer and Packagist behaviour](repository-transfer-and-packagist.md) for metadata hygiene).
- Consumers choose the line with a **version constraint**, not a branch name:
  - Monolog 3: `composer require mhcg/monolog-wp-cli:^3.0`
  - Monolog 2 (maintenance): `composer require mhcg/monolog-wp-cli:^2.3`

Do not create a second Packagist package for the legacy line.

## What consumers install

Tagged releases expose `composer.json` **`require`** and **`autoload`** only. Consumers resolve their own dependency trees.

The repository **`composer.lock`** on each branch is for **maintainers and CI** (reproducible `composer install`, `composer audit --locked`). A dev-only advisory in the lockfile does **not** mean installed sites pulled the vulnerable package unless it appears in that tag’s runtime `require`.

## Tagging and GitHub Releases

1. Complete work on the correct branch (`main` or `2.x`).
2. Run `composer run qa` on that branch; run WordPress smoke when behaviour or runtime deps change ([CONTRIBUTING.md](../.github/CONTRIBUTING.md)).
3. Run `composer run audit:locked` (or `composer audit --locked --no-interaction`) before tagging.
4. Create an annotated tag matching the semver major of the branch (`v3.x.x` on `main`, `v2.x.x` on `2.x`).
5. Push the tag; publish a GitHub Release with notes (advisory IDs when relevant).

Publishing the GitHub Release triggers the Hugo site deploy workflow for user documentation.

### When a patch tag is required

| Change | Tag? | Notes |
| --- | --- | --- |
| `src/` or first-party security fix | **Yes** | Patch on the affected branch; backport per [security maintenance](security-maintenance.md). |
| Runtime `require` advisory (e.g. `monolog/monolog`) | **Yes** | Bump constraints or lock as needed; patch tag after CI. |
| `require-dev` / lockfile only (PHPCS, PHPUnit, transitive dev tools) | **Usually no** | Fix the branch promptly; consumers unaffected. Optional patch tag if you want a visible audit-clean snapshot—state “no code changes” in release notes. |
| CI, Dependabot, maintainer docs only on one branch | **No** unless you voluntarily tag | No Packagist consumer action needed. |

## Security audit workflow (both branches)

[`.github/workflows/security-audit.yml`](../.github/workflows/security-audit.yml) runs on schedule and `workflow_dispatch`. It checks out **`main`** and **`2.x`** and runs `composer audit --locked` for each.

- A failure on **one matrix leg only** affects that ref’s lockfile, not necessarily the other line’s consumers.
- Interpret failures with [security maintenance — triage](security-maintenance.md#triage): separate runtime from dev-only.
- Re-run via Actions → Security audit → Run workflow after fixing the failing branch.

## Keeping branches aligned (without merging everything)

| Mechanism | Purpose |
| --- | --- |
| [Dependabot `target-branch: 2.x`](../.github/dependabot.yml) | Composer and Actions updates on the maintenance line |
| Cherry-pick / small PRs | Shared `src/` security fixes, scripts, or docs that apply to both lines |
| **Avoid** wholesale merge of `main` → `2.x` for `.github/workflows/php.yml` | `2.x` uses a different PHP matrix (7.2–8.5). Review differences per [security maintenance](security-maintenance.md#responding-on-2x). |

When `main` gains maintainer tooling (for example `audit:locked`), mirror the equivalent on `2.x` when that branch is next touched.

## Coding agent checklist

Before dependency, release, or security work:

1. **Identify branch** — `main` (Monolog 3) vs `2.x` (Monolog 2). Do not apply `main` PHP/Monolog constraints on `2.x` or vice versa.
2. **Read** — `composer.json` on the current branch, [php version strategy](php-version-strategy.md), and [security maintenance](security-maintenance.md).
3. **Classify advisories** — runtime `require` vs `require-dev`; confirm with `composer audit --locked` on that branch checkout.
4. **Fix on branch** — lockfile or constraint updates on the branch that failed; prefer Dependabot PRs targeting `2.x` when available.
5. **Verify** — `composer run qa`; WordPress smoke when runtime or `src/` changed.
6. **Tag only when the table above requires it** — use the correct semver major from the branch you tagged.
7. **Packagist** — no manual step if the package is linked; confirm the new tag appears on [Packagist](https://packagist.org/packages/mhcg/monolog-wp-cli).

For support-policy or CI matrix edits, use the `support-governance` skill and keep user-facing site copy in sync.
