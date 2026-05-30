---
agent: 'maintain-version-policy'
model: GPT-5.3-Codex
description: Refresh the repository's version-support policy sources of truth by checking current WordPress and PHP support windows, reviewing package compatibility documents, and updating policy wording and tuple snapshots.
---

Maintain the version-support policy for this repository.

Use `.github/skills/support-governance/SKILL.md` as the central operating guide.

Focus on policy and source-of-truth maintenance:

- inspect current upstream WordPress and PHP support status
- review repository compatibility and policy documents
- update or propose updates to internal and public support-policy wording
- report policy drift, changed tuples, and any follow-up work needed in tests or CI

When complete, summarise:

- which policy files changed
- which upstream facts were checked
- what support windows or tuples changed
- what test or CI follow-up is still needed, if any