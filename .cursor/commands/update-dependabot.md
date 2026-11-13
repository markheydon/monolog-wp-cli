# Update `dependabot.yml`

Review the current Dependabot config and update or create `.github/dependabot.yml`. The config should be minimal, consistent, and aligned with what this repository actually uses.

Repo `dependabot.yml` files can easily become inconsistent or overly complex. Apply the same simple structure across repositories.

## Input

- The existing `.github/dependabot.yml` file (if present).
- Detect package ecosystems from the repository (see [supported ecosystems](https://docs.github.com/en/code-security/reference/supply-chain-security/supported-ecosystems-and-repositories)).

For **monolog-wp-cli**, typical ecosystems:

| Ecosystem | When to include |
| --- | --- |
| `composer` | Root `composer.json` (library and dev tools) |
| `github-actions` | Always |
| `devcontainers` | `.devcontainer/devcontainer.json` exists |
| `gomod` | Only if Dependabot should track `website/go.mod` (Hugo theme module); omit if theme bumps are handled manually |

Do not add ecosystems with no manifest in the repo (for example npm without `package.json`).

## Instructions

1. Keep the configuration minimal. Do not introduce optional fields unless explicitly required.
2. Standardise scheduling:
   - Weekly
   - Monday
   - 04:00
   - Timezone: `Europe/London`
3. Ensure **github-actions** updates are always included.
4. Ensure **devcontainers** updates are included when `.devcontainer/devcontainer.json` is present.
5. Include only relevant package ecosystems based on repo contents.
6. **open-pull-requests-limit:** default `5`; use `2` only for noisy ecosystems if needed.
7. Grouping to reduce PR noise:
   - Group **minor** and **patch** together per ecosystem where appropriate
   - Separate **major** groups per ecosystem (mirror existing `composer-minor-patch` / `composer-major` and `actions-*` patterns in this repo)
   - Avoid over-engineering group definitions
8. Labels:
   - Prefer `gh label list` from the repo root when `gh` is available.
   - If `dependencies` exists on GitHub, use `dependencies` only (this repo's convention).
   - If the check cannot be made, use `dependencies` only. Do not guess from docs.
9. Assignees: always `markheydon`.
10. Do **not** include:
    - `commit-message` blocks
    - unnecessary `allow`/`ignore` rules
    - excessive or overly specific grouping
11. Output clean, readable YAML with consistent formatting.

## PHP / Composer notes

- Group Composer dev tools with the same minor/patch vs major pattern as runtime packages unless a documented pin requires an `ignore` entry.
- Do not add NuGet-, npm-, or .NET-specific groups or ignore rules from other templates unless this repo gains those ecosystems.

## Output

- Update the repository's `.github/dependabot.yml` only.
- Brief explanation of what changed (not a full diff dump).
- No other commentary unless it adds real value.
