---
agent: 'check-support-test-alignment'
model: GPT-5.3-Codex
description: Check whether WordPress smoke scripts, Docker image tags, Composer scripts, CI matrices, and support documentation still match the repository's documented support policy, then repair or report drift.
---

Check support-to-testing alignment for this repository.

Use `.github/skills/support-governance/SKILL.md` as the central operating guide.

Focus on executable coverage and drift detection:

- verify that documented WordPress/PHP tuples are runnable and exercised
- inspect Docker image tags, local smoke scripts, Composer scripts, and CI workflow entries
- update implementation files where the current policy is already clear
- report any policy ambiguities that block full alignment

When complete, summarise:

- which implementation files changed
- which tuples are exercised after the update
- any remaining mismatch between documented policy and executable coverage