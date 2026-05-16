---
agent: 'update-docs'
model: GPT-5.3-Codex
description: Update repository documentation by refreshing README.md and syncing public docs in docs/ with the current codebase and policy.
---

Update docs for this repository.

Use `.github/agents/update-docs.agent.md` as the operating guide.

Required outcomes:

- Refresh `README.md` using `.github/skills/repo-readme-generator/SKILL.md`.
- Use the README skill for `README.md` only, not for generating `docs/` pages.
- Use `.github/skills/documentation-writer/SKILL.md` for Diataxis-driven updates to `docs/` pages.
- Sync public docs in `docs/` with current code and policy.
- Apply Diataxis intent to each updated page in `docs/` (tutorial, how-to, reference, explanation).
- Keep changes evidence-based, concise, and technical.
- Keep wording in UK English.
- Do not modify `docs-internal/` unless explicitly requested.

When complete, summarise:

- Which docs files changed.
- Which source-of-truth files were used.
- Any remaining docs gaps or follow-ups.
