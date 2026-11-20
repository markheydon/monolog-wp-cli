# Update `dependabot.yml`

Review the current Dependabot config. And update or create a `.github/dependabot.yml` file. The config should be a clean, consistent, minimal configuration that follows best practices and reduces noise, while managing dependencies for relevant ecosystems based on the repo contents.

Repo `dependabot.yml` files can easily become inconsistent, overly complex, and include unnecessary configuration. So my policy is for a simple, standard structure applied across all repositories based on the following.

## Input

- The existing `.github/dependabot.yml` file in the repo (if there is one).
- Detect package ecosystems from the repository (e.g. NuGet for .NET, npm for JS, etc.).
- The supported ecosystems list from GitHub Docs https://docs.github.com/en/code-security/reference/supply-chain-security/supported-ecosystems-and-repositories.

## Instructions

1. Keep the configuration minimal, do NOT introduce optional fields unless explicitly required.
2. Standardise scheduling:
   - Weekly updates
   - Monday
   - 04:00
   - Timezone: Europe/London
3. Ensure GitHub Actions (github-actions) updates are ALWAYS included.
4. Ensure Dev Containers (devcontainers) updates are included if there is a `.devcontainer` folder with a `devcontainer.json` file in the repo.
5. Include only relevant package ecosystems based on what is actually used.
6. Apply a sensible open-pull-requests-limit:
   - Default: 5
   - Lower (e.g. 2) only for noisy ecosystems like SDKs if present
7. Add grouping to reduce PR noise:
   - Group patch + minor updates together where appropriate
   - Create logical groups based on ecosystem:
     - For .NET:
       - test packages (xunit, Moq, Microsoft.NET.Test.Sdk, coverlet, etc.)
       - Microsoft.Extensions.*
       - Azure.*
       - OpenTelemetry.*
       - analyzers
   - Avoid over-engineering group definitions
8. Labels:
   - When you can, confirm labels on GitHub (prefer `gh label list` from the repo root). Do **not** infer labels from `plan/LABEL_STRATEGY.md`, issue templates, or other docs; those may describe labels that are not created on GitHub yet.
   - If the check succeeds and `type/chore` is present, use `type/chore` only.
   - If the check succeeds and `type/chore` is absent, use the default GitHub label `dependencies` only.
   - If the check cannot be made (no `gh`, not signed in, no network, wrong remote, or any error), use `dependencies` only. Do not guess.
9. Assignees:
   - Always assign PRs to: markheydon
10. Do NOT include:
   - commit-message
   - unnecessary allow/ignore rules
   - excessive or overly specific grouping
11. Ensure the output is clean, readable YAML with consistent formatting.

## Special Cases

### Microsoft.OpenApi package in C# projects

Some repos have pinned 'Microsoft.OpenApi' in their `Directory.Packages.props` file due to a known vulnerability in a dependent package.

Microsoft.OpenApi's versions are aligned to specific .NET versions therefore the following 'ignore' is required to stop Dependabot trying to update to an incompatible version.

    ignore:
      # Microsoft.OpenApi 3.x is not compatible with ASP.NET Core 10; stay on 2.x until a future ASP.NET release.
      - dependency-name: Microsoft.OpenApi
        update-types:
          - version-update:semver-major

### LTS repos: Microsoft.Extensions.* major versions

While the app targets the current LTS runtime (e.g. net10.0), do not take `Microsoft.Extensions.*` semver-major updates (11.x while on .NET 10). A `microsoft-extensions` group with `update-types: [minor, patch]` is not enough: Dependabot still opens **individual** PRs for majors. Add a job-level ignore:

    ignore:
      - dependency-name: "Microsoft.Extensions.*"
        update-types:
          - version-update:semver-major

## Notes

- The `update-types` option for groups supports a different set of levels than other uses of `update-types`. Supported values: `minor`, `patch`, and `major`.
- .NET SDK (dotnet-sdk) tends to be too keen at updating to versions that aren't supported in all dev environments so rarely a good idea to include in the config.

## Output

- ONLY an updated, final `dependabot.yml`, which you will update the repo's version with.
- Very brief explanation of what changed, not the full diff.
- No other comments unless they add real value.
