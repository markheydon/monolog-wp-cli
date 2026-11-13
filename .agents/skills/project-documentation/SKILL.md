---
name: project-documentation
description: >-
  Write or update repository documentation (README, docs/, website/content/) using
  Diátaxis and project context (AGENTS.md, composer.json, policy docs). Use when
  drafting tutorials, how-tos, reference, explanations, or choosing README vs
  docs/ vs Hugo site placement.
---

# Project documentation

## Instructions

1. Pick the Diátaxis document type and workflow in [references/diataxis.md](references/diataxis.md).
2. Read context before drafting: [AGENTS.md](../../../AGENTS.md), `composer.json`, relevant `src/` and `tests/`, and policy docs under `docs/` when the topic touches support, naming, or README badges.
3. Choose the file location using [references/document-placement.md](references/document-placement.md).
4. For `README.md`, follow [references/readme-packagist.md](references/readme-packagist.md). For `website/content/`, follow [references/hugo-site.md](references/hugo-site.md).
5. Use a starter from [assets/](assets/) when it fits the document type.
6. Finish with [references/review-checklist.md](references/review-checklist.md).

## Boundaries

- Support policy and CI tuple alignment: use `support-governance` instead of duplicating policy here.
- When context files and existing docs disagree, flag the mismatch.

## Writing rules

- Concise, task-oriented prose; reflect the real repo layout.
- Prefer short README sections with links to the documentation site for depth.
- User-facing copy in `README.md` and `website/content/` must not name documentation frameworks (Diátaxis, quadrants, and similar). Section nav labels such as Tutorials or How-to are fine.
- Use WordPress Coding Standards for PHP snippets in user-facing docs.

## Resources

| Path | Purpose |
| --- | --- |
| [references/diataxis.md](references/diataxis.md) | Diátaxis types and writing workflow |
| [references/document-placement.md](references/document-placement.md) | README vs `docs/` vs `website/content/` |
| [references/review-checklist.md](references/review-checklist.md) | Final review |
| [references/hugo-site.md](references/hugo-site.md) | Hugo front matter and local build |
| [references/readme-packagist.md](references/readme-packagist.md) | README and Packagist rules |
| [assets/readme-section-template.md](assets/readme-section-template.md) | README sections |
| [assets/how-to-template.md](assets/how-to-template.md) | How-to guides |
| [assets/tutorial-template.md](assets/tutorial-template.md) | Tutorials |
| [assets/technical-blog-template.md](assets/technical-blog-template.md) | Technical blog posts |
