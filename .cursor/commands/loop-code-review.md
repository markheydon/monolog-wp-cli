# Looped code review

## Your job

Repeat this cycle until the review is clear of actionable findings:

1. **Review** - In a **new subagent** (Task), read and follow [.agents/skills/code-review/SKILL.md](../../.agents/skills/code-review/SKILL.md). Use the default diff (merge-base with the repository default branch, including committed, staged, and unstaged work) unless the user scoped the run differently. The subagent must return findings only; it must not implement fixes.
2. **Fix** - In this session, implement every finding you agree with using minimal, convention-matching diffs. Skip nits you would not block merge on; note them briefly if useful.
3. **Verify** - From the repo root, run `composer run qa` per [AGENTS.md](../../AGENTS.md). When the change touches WordPress smoke paths or documented runtime tuples, also run `composer run test:wp`. Fix any failures before the next loop.
4. **Commit** - When a loop produced code changes, create a commit with a message that reflects the review fixes (invoking this command counts as explicit permission to commit).

Start the next loop only after steps 2–4 are done (or step 1 returned no significant issues).

Do **not** substitute Bugbot (`/review-bugbot`) or security (`/review-security`) subagent reviews for this loop unless the user asks; the code-review skill describes when to use those instead.

## Stop condition

- **All clear** - The code-review skill reports no significant issues (explicit all-clear). Then: if there is an open PR for this branch, push and work it until merge-ready (CI green, conflicts resolved, review feedback addressed) per [.github/PULL_REQUEST_TEMPLATE.md](../../.github/PULL_REQUEST_TEMPLATE.md) and [.github/CONTRIBUTING.md](../../.github/CONTRIBUTING.md).
- **Cap** - After **6** complete review cycles, stop. Summarise remaining findings, what you fixed, and whether continuing the loop is worthwhile or accepting the current state is reasonable.

## Isolation

Run **each review cycle in its own subagent** so review context does not accumulate across loops. Implementation, build/test, and commits stay in the parent session so fixes are applied once and verified consistently.
