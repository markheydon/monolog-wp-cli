---
name: Address PR Review
description: Review all open comment threads on a pull request, fix the issues raised, reply to each thread explaining what was done, and resolve the conversation.
argument-hint: PR number to address, e.g. "PR 25" or "PR 25 in owner/repo"
---

## When to use this prompt

- **When a PR has received a code review** with open comment threads.
- **Before merging** — ensures no review feedback is silently ignored.
- **Time:** 5–15 minutes depending on the number of threads and complexity of fixes.

## What you'll get

- All open review threads read and understood.
- Code changes applied for each actionable thread.
- A reply posted to each thread explaining what was changed (or why no change was made).
- Each resolved thread marked as **Resolved**.
- A summary of what was done.

## What comes next

After running this prompt:
- Check the PR on GitHub to confirm all threads are resolved.
- If any threads were marked "no change needed", review the reasoning and decide whether to accept it.
- Once all threads are resolved, the PR is ready to merge.

---

## Step 1 — Identify the PR

If a PR number was provided, use it. If not, ask: "Which PR number should I address the review for? (And which repo if not the current one?)"

Confirm the PR number and repo before proceeding.

---

## Step 2 — Read all open review threads

Use `@github` to fetch all open (unresolved) review comment threads on the PR.

For each thread, extract and list:
- **Thread ID**
- **File and line number**
- **Reviewer comment** (full text)
- **Category** (one of: `fix-required`, `question`, `suggestion`, `nitpick`, `praise`)

Present this list as a table and confirm with the developer before making any changes:

```
| # | File | Line | Category | Summary of comment |
|---|------|------|----------|--------------------|
| 1 | ... | ... | fix-required | ... |
```

If there are no open threads, report that and stop.

---

## Step 2.5 — Thread Reply Transport Rules (Critical)

Before posting any reply, confirm you can post **into the existing thread itself**.

Allowed methods for thread replies:
- Reply directly to the existing review comment/thread using a thread/comment reply API.
- Add a review comment that is explicitly attached to the same file/line thread in a pending review, then submit that review.

Not allowed:
- Posting a normal PR/issue comment as a substitute for a thread reply.
- Posting a batch of top-level PR comments that reference thread IDs.

If thread-level reply tooling is unavailable:
1. Stop before resolving any threads.
2. Report the limitation clearly.
3. Provide the exact reply text per thread so the developer can paste it manually.
4. Do **not** resolve threads in this fallback path.

---

## Step 3 — Address each thread in order

Work through threads one at a time. For each thread:

### If category is `fix-required` or `suggestion` (actionable):

1. **Make the code change** — apply the fix in the relevant file. If the fix is ambiguous, state your interpretation before applying it.
2. **Post a reply to the thread** in this format:
   ```
   Fixed. [One sentence describing what was changed and where.]
   ```
3. **Resolve the conversation.**

### If category is `question`:

1. **Post a reply** answering the question based on the code and context.
2. If the question implies a code change is needed, make the change and note it in the reply.
3. **Resolve the conversation.**

### If category is `nitpick`:

1. Apply the change if it is trivially safe (e.g. rename, formatting, comment wording).
2. **Post a reply:**
   ```
   Applied. [Brief note on what was changed.]
   ```
   Or, if not applied:
   ```
   Noted but not changed — [brief reason].
   ```
3. **Resolve the conversation.**

### If category is `praise`:

1. **Post a reply:** `Thanks for the kind words!`
2. **Resolve the conversation.**

---

## Step 4 — Summary

When all threads have been addressed, output a summary table:

```
| # | File | Comment summary | Action taken | Resolved |
|---|------|-----------------|--------------|---------|
| 1 | ... | ... | Fixed: renamed variable to... | ✅ |
| 2 | ... | ... | No change — existing behaviour is intentional because... | ✅ |
```

Then state: "All [n] threads resolved. PR is ready for final review before merge."

---

## Rules

- **Never resolve a thread without posting a reply first.** The reply is the audit trail.
- **Never silently skip a thread.** If a fix cannot be applied (e.g. out of scope, disagree with suggestion), post a reply explaining why and still resolve.
- **Never use top-level PR/issue comments as a proxy for thread replies.**
- **Do not make unrequested changes** to files not referenced in a review thread.
- **One reply per thread** — do not post multiple comments on the same thread.
- **Use UK English** in all replies.