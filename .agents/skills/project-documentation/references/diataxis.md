# Diátaxis framework

Technical documentation follows the [Diátaxis framework](https://diataxis.fr/). Use UK English and repository rules in [AGENTS.md](../../../AGENTS.md).

Diátaxis guides **authoring and placement** for agents and maintainers. Do not name the framework in user-facing `website/content/` or `README.md` prose.

## Guiding principles

1. **Clarity:** Simple, clear, unambiguous language.
2. **Accuracy:** Code snippets and technical details match the codebase and current behaviour.
3. **User-centricity:** Every document helps a specific reader achieve a specific goal.
4. **Consistency:** Tone, terminology, and style align with existing project docs.

## Four document types

Pick one primary type per document. Do not blend types in a single page without clear structure.

| Type | Orientation | Purpose |
| --- | --- | --- |
| **Tutorial** | Learning | Practical steps for a newcomer to a successful outcome. |
| **How-to** | Problem | Steps to solve a specific problem. |
| **Reference** | Information | Technical descriptions of machinery (APIs, config, CLI). |
| **Explanation** | Understanding | Clarifies why or how something works. |

## Workflow

1. **Clarify** - Establish document type, target audience, reader goal, and scope (include and exclude). Ask only when the request leaves these ambiguous.
2. **Outline** - Propose a short structure (headings or table of contents). Skip the outline step when the user asked for a quick fix or a full draft in one pass.
3. **Draft** - Write Markdown. Ground claims in repository context; do not invent product behaviour.
4. **Context** - Use provided repo files for tone and terminology. Do not copy large blocks unless asked. You may use diataxis.fr and links the user supplies.
