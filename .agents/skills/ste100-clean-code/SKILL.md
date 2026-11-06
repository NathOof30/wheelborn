---
name: ste100-clean-code
description: ASD-STE100 technical writing discipline in English, unnecessary comment elimination (/no-comments), and removal of AI text noise (/unslop). Use in all responses, documentation, commits, PRs, and PHP/Blade code.
---

# ASD-STE100 Technical Writing & Clean Code (`ste100-clean-code`)

## 1. Goal
Maintain all communications, documentation (`SPEC`, `GLOSSARY.md`, PR descriptions), and source code completely free of empty AI jargon (*slop*) and redundant narrative comments, applying the **ASD-STE100** standard adapted for software engineering.

## 2. Non-Negotiable Text Rules (ASD-STE100 + `/unslop`)
- **Use terminological consistency (1 term = 1 concept):** Always refer consistently to `Action`, `DTO`, `Seam`, and `Vertical Slice`.
- **Write in active voice:** Prefer "The Action updates the order" over "The order is updated by the Action".
- **Keep sentences short:** Limit procedural instructions to a maximum of 20 words and descriptive statements to a maximum of 25 words.
- **Give one instruction per sentence:** Break multiple steps into numbered lists.
- **Eliminate AI conversational crutches (`/unslop`):** Strictly ban empty phrases such as *"Certainly!"*, *"Let's dive in"*, *"It is important to note that"*, *"Robust and scalable"*, *"Without further ado"*. State technical facts directly.
- **Write commit messages with proof:** Title states what changed. Body states why it changed and how it was proved. Proof is the command and `[measured]` output, for example: `php artisan test --filter=ApproveOrder: 3 passed`. Without execution, write `unverified` and the reason.

## 3. Non-Negotiable Code Rules (`/no-comments`)
- **Do not narrate what code already says:** Remove all comments that simply restate method names or variables (e.g., `// Find user by ID`).
- **Encode constraints in structure, not comments:** When tempted to write a comment warning about a type or state, replace the comment with a strict type, an `enum`, a `readonly` DTO, or an assertion.
- **When a comment is permitted:**
  1. Type annotations required by PHPStan/Larastan (e.g., `/** @var Collection<int, Order> $orders */`).
  2. Explanation of a non-obvious *why* (workaround for an external upstream bug, RFC link, or measured performance justification).
- **Subtract before adding:** Delete commented-out code, unused methods, and orphaned imports in the same commit.

## 4. Forbidden Rationalizations (Anti-Shortcuts)
| Agent Excuse | Required Rule |
|---|---|
| "I will leave comments `// Step 1`, `// Step 2` inside the `handle()` method for clarity." | **Forbidden.** If a method needs comment dividers, extract smaller private methods with descriptive names. |
| "I will comment out old code instead of deleting it, in case we need it later." | **Forbidden.** Git stores history. Delete dead code immediately. |
| "I will write a polite introductory paragraph praising the user's question." | **Forbidden.** Start the response directly with the decision, diagnosis, or result. |
| "The title `fix: resolve approval` is enough; the diff shows the rest." | **Forbidden.** The diff shows what, not why or how it was proved. Write both in the body. |

## 5. Execution Proof (Exit Criteria)
- `[measured]` Zero commented-out code or narrative comments (`// validate data`, `// save to db`) in the `git diff`.
- `[measured]` Spec, ticket, or PR text written in active voice and short sentences under ASD-STE100 guidelines.
- `[measured]` Body of each commit contains the rationale and proof, or `unverified` with the reason.
