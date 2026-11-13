# Maintainer documentation

Internal and technical reference notes for maintainers and coding agents.

Use this folder for:

- Operational decisions and rationale.
- Repository maintenance policies.
- Historical context that should not be lost between sessions.

Keep entries concise and factual. Prefer one file per topic.

Public user documentation lives in [`website/content/`](../website/content/). When drafting or updating maintainer notes here, use the `project-documentation` skill in [`.agents/skills/project-documentation/`](../.agents/skills/project-documentation/SKILL.md).

## Index

- [Dual-branch releases and Packagist](dual-branch-releases.md)
- [Package identity and naming](package-identity.md)
- [PHP version strategy](php-version-strategy.md)
- [WordPress runtime support policy](wordpress-support-policy.md)
- [PSR-3 to WP-CLI level mapping](psr3-to-wp-cli-level-mapping.md)
- [Repository transfer and Packagist behaviour](repository-transfer-and-packagist.md)
- [README and badge policy](readme-and-badges.md)
- [Test under WordPress and WP-CLI](test-under-wordpress.md)
- [Security maintenance](security-maintenance.md)

## Consumer upgrade guide

Package users should follow [UPGRADE.md](../UPGRADE.md) at the repository root (linked from the README and the public compatibility page).
