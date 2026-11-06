---
name: leitura-independente
description: A fresh Reader subagent, without chat history, reads a draft Spec or ticket plan before recording. It returns the Paraphrase and findings; the author resolves each finding with the user. Use in /especificar and /planejar before persisting documents.
---

# Independent Reading of Specs and Tickets (`leitura-independente`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
The implementer reads the Spec and ticket without the chat history. An unstated assumption becomes an unspoken design decision. Before recording, a fresh Reader reads the draft under the exact same conditions. It returns the Paraphrase and findings. The Author resolves each finding with the user.

## 2. Terms
- **Author:** The main agent that wrote the draft, possessing chat history.
- **Reader:** A fresh subagent or clean session without chat history. It only reads the draft, glossary, configuration, ADRs, and code.
- **Paraphrase:** The Reader's text explaining in its own words what the document proposes and how the current system behaves according to the document.
- **Open Decision:** A design choice left unaddressed by the document.

## 3. Non-Negotiable Rules
- **Use a fresh Reader for each review:** Do not reuse previous subagents or contexts that inherit bias.
- **Pass pointers only:** Send the review mode, draft path, approved Spec, glossary, ADRs, and configuration. Do not send chat transcripts or rationales.
- **Write drafts to temporary storage outside repo:** Use `$TMPDIR` or local scratch paths.
- **The Reader only reads:** It does not edit code, run commands, or spawn subagents.
- **Do not read within the same context:** The Author reads what they intended to write, not what was written. Without subagents, ask the user or explicitly mark `Independent Reading: unverified (same context)`.

## 4. Reader Review Checklist
1. **Reading & Paraphrase:** State what the plan delivers in 1 sentence per ticket.
2. **Code Validation:** Check that files to modify exist and files to create do not exist yet.
3. **Findings Search:** Identify contradictions, open decisions left to the implementer, undefined terms, undeclared side-effects, and missing test criteria.
4. **Severity:**
   - `[BLOCKER]`: Contradiction, uninterpretable section, or open decision altering observable behavior, data schema, or authorization.
   - `[IMPORTANT]`: Missing test scenario, incorrect Context Pointer, or undocumented dependency.
