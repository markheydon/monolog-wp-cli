# PHP Support Policy Framework

## Purpose

Define a repeatable policy for PHP support that keeps this package aligned with Monolog majors while giving maintainers clear release decisions.

## Baseline principles

- Monolog major compatibility is the primary driver of runtime PHP support.
- `composer.json` `require.php` reflects consumer runtime support, not maintainer workstation defaults.
- Raising minimum PHP beyond the targeted Monolog floor is a breaking policy decision and must be justified in release notes.

## Decision rules (apply in order)

- Identify the active Monolog line for the release branch.
- Set the default PHP floor to that Monolog line’s documented minimum:
  - Monolog `^2.5` line: PHP `7.2+`
  - Monolog `^3` line: PHP `8.1+`
- Raise above that floor only if one of these is true:
  - Required language/runtime features cannot be reasonably avoided.
  - Security, platform, or dependency constraints make lower versions non-viable.
  - Support cost is no longer operationally sustainable.
- If raised above the Monolog floor, treat as a breaking change and document rationale, impact, and upgrade path.

## Release and migration approach

### Monolog 2 line

- Keep a maintained release line compatible with `monolog/monolog:^2.5` for users needing legacy PHP ecosystems.
- Limit changes to fixes, compatibility maintenance, and low-risk improvements.
- Avoid accidental adoption of PHP features above the declared floor.

### Monolog 3 line

- Deliver Monolog 3 support as a separate major release.
- Move PHP floor to `8.1+` in the same major transition.
- Publish explicit migration notes covering:
  - Composer constraint changes
  - Any API/behavior changes tied to Monolog 3
  - Suggested upgrade order for consumers

### Branching and support window

- Use separate maintenance branches when both lines are active.
- Define and publish support windows (active vs security-only) for each line.
- Backport only fixes that are relevant and safe for the older line.

## Handling tooling/CI vs runtime policy conflicts

- If QA tools require newer PHP than runtime target:
  - Run tooling on newer PHP jobs.
  - Keep at least one install/test job at the declared runtime minimum.
- If a tool cannot run on the runtime minimum, do not automatically raise runtime support.
- Prefer per-branch tool version pinning, matrix splits, or separate quality gates before changing consumer PHP constraints.
- Only raise runtime floor after an explicit policy decision and major-release communication.

## Current application to this repository

- Current constraints are `php:^8.3` and `monolog/monolog:^2.5`.
- Under this policy, maintainers must choose explicitly between:
  - **Alignment path:** lower PHP floor to the Monolog 2-compatible floor for this line.
  - **Intentional restriction path:** keep `^8.3`, document why this line is stricter than Monolog 2, and treat that as product policy.
- Monolog 3 adoption should be planned as the next major line with PHP `8.1+` minimum.

## Decision checklist

- Which Monolog major does this release line target?
- Does `require.php` match that Monolog major’s minimum policy?
- If stricter, is the reason explicit, valid, and documented?
- Is the change breaking, and is release versioning aligned?
- Does CI distinguish runtime minimum validation from tooling convenience jobs?
- Are migration notes and branch/support expectations clear to consumers?
