---
name: revisar
description: Executes Adversarial Review in clean context (Standards + Spec + Livewire/Laravel Security), with each finding proved by execution, and generates a concise PR summary with Blast Radius. Use when the user types /revisar.
disable-model-invocation: true
---

# Adversarial Review & Delivery Briefing (`/revisar`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Review the delivery diff in a single round across 2 isolated axes: Standards (Laravel/Livewire patterns & security) and Spec. Keep only findings proven by execution. Afterwards, write a concise PR briefing with the calculated Blast Radius.

Review from the perspective of the developer receiving alerts when changes break in production. In each axis, prioritize in this order: correctness, security, expected load resilience, test coverage, execution speed.

## 2. Non-Negotiable Rules
- **Use isolated reviewers per worktree:** With subagents, launch 2 parallel, isolated reviewers (one per axis). Each reviewer receives their own worktree with the reviewed state. Use fresh reviewers for each round. Pass only Context Pointers: the worktree, diff file, Flow Map, Spec path, glossary, and project configuration.
- **Without subagents, declare context:** Inform the user that single-context review is not independent and offer `/revisar` in a new session without implementation history. If the user continues in the current session, review in place and mark `Independence: same context` in the report.
- **Report only; do not edit:** `/revisar` does not edit target code. Proof files remain outside user checkout. The user decides on findings.
- **Prove each finding by execution:** Every finding must include a concrete case and reproducible proof: proof file, exact command, actual output, and output required by the Spec. Reading spots suspicions; execution proves them. Pure stylistic preferences, naming tastes, or speculative risks without proof are excluded.
- **Execute only in test environments:** Proofs must run as automated tests or test-environment scripts. Never run destructive migrations or external services outside testing databases.
- **Record unverified risks:** Some risks cannot be executed in standard test environments (multi-server concurrency races, massive table memory exhaustion, scheduler overlaps). Document these under `Unverified:` with file:line and reasons, outside the `Verdict:`.
- **Trace every modified value end-to-end:** A Flow is the path of a value modified by the diff (key, ID, column, status, rule, return signature) from entry to every consumer. Search callers using grep/ripgrep. One verified consumer does not prove others.
- **Keep axes separate:** Standards and Spec remain in separate sections. Do not merge or reorder findings across axes. Approval on one axis does not offset blockers on the other.
- **Severity classification:**
  - `[BLOCKER]`: Bug, security vulnerability, data loss, or crash under expected load.
  - `[IMPORTANT]`: Untested risky business logic, missing specified requirement, or measured latency regression.

## 3. Process
1. **Diff, gate, and worktrees:**
   - Inspect `git status` and target diff. Record the exact reference (e.g., `main...HEAD`). Save the diff to a temporary file.
   - Run the complete verification gate from `laravel-verification`.
2. **Flow Map:**
   - List values modified by the diff.
   - For each value, search all reading and writing locations across Models, Actions, Livewire, Blade, Policies, and tests.
   - Record each Flow as: `value: input → consumer → output` with `file:line`.
3. **Review across 2 axes:**
   - **Standards Axis (Laravel/Livewire architecture & security):**
     - Livewire properties and IDs protected with `#[Locked]`.
     - Mutable actions authorized via policies or `$this->authorize()`.
     - Data integrity: multi-write operations wrapped in `DB::transaction()` with pessimistic locking (`lockForUpdate()`) where needed.
     - Concurrency: race condition prevention, unique constraints, idempotency on repeated executions.
     - Performance: N+1 query prevention, memory streaming for large tables (`chunkById()`, `lazy()`).
   - **Spec Axis (Functional behavior & requirements):**
     - Compare diff line-by-line with feature specifications. Identify missing requirements or unrequested scope creep.
     - Edge cases: null, empty collections, zero values, boundary thresholds.
     - Missing test coverage for high-risk logic.
4. **Output format:**
   - Report findings categorized by axis and severity with exact execution proofs.
   - Conclude with clear Delivery Verdict (`can deliver` or `requires fixes`).
