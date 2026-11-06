---
name: planejar
description: Decomposes an approved Specification (Spec) into a graph of Vertical Slices with a Throughput Checkpoint, using configured ticket destinations. Before recording, a fresh Reader inspects the tickets. Use when the user types /planejar.
disable-model-invocation: true
---

# Plan Graph of Vertical Slices (`/planejar`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Transform an approved Specification (Spec) into small Vertical Slice (*Tracer Bullet*) tickets with explicit blocking dependencies and a mandatory *Throughput Checkpoint* prior to execution.

## 2. Non-Negotiable Rules
- **Forbid horizontal slicing:** Never slice tasks by isolated technical layers (e.g., "Ticket 1: Create all migrations", "Ticket 2: Create all Actions"). Every ticket must deliver an end-to-end verifiable vertical slice.
- **Use expand–contract for broad refactoring:** Mechanical changes breaking many call sites at once (renaming columns, altering shared DTO or Enum types) do not fit in a single vertical slice. Sequence into 3 stages:
  1. **Expand:** Add the new format alongside the old one. Nothing breaks.
  2. **Migrate:** Migrate call sites in batches (by module or directory). Each batch is a ticket blocked by Expand and turns green independently.
  3. **Contract:** Remove the old format in a ticket blocked by all migration batches.
- **Write the Throughput Checkpoint before tickets:** Separate blocking foundations from parallelizable streams and identify files with shared mutable state.
- **Declare blocking edges:** Every ticket must explicitly specify `Blocked by: None` or `Blocked by: Ticket #N`.
- **Use the Tickets Destination:** Read the configured destination (`tickets/todo/` or issue tracker). Ask only if the choice is absent or conflicting.

## 3. Process
1. **Spec Reading:** Read the designated Spec. Inspect affected files and identified dependencies.
2. **Throughput Checkpoint (4 mandatory sections):**
   - **1. Blocking Foundation:** What must exist before any parallelism (e.g., shared schema migration or core contract).
   - **2. Parallelizable Streams:** Which vertical slices do not touch the same files and can be executed independently.
   - **3. Shared Mutable State:** Files that multiple tickets would touch (e.g., `routes/web.php`, common HUD component) and how to sequence edits.
   - **4. Smallest Safe Decomposition:** Minimum number of verifiable commits/tickets (~50-100 lines per slice).
3. **Structure of Each Ticket (Vertical Slice):**
   - **Title and Observable Goal** (1 sentence in active voice).
   - **Blocked by:** `#ID` or `None`.
   - **Context Pointers:** Exact paths and line ranges of files to create or modify.
   - **Acceptance Criteria in Test:** Specific automated test asserting the public Seam and Boundary (`Livewire::test()` or HTTP test) proving the slice.
4. **Independent Review:** Validate the ticket plan to ensure no implicit design decisions are left unstated for the implementer.
5. **Persistence:** Write tickets to destination (`tickets/todo/NN-slug.md` or issues).

## 4. Forbidden Rationalizations (Anti-Shortcuts)
| Agent Excuse | Required Rule |
|---|---|
| "I will create a separate ticket at the end just to write tests." | **Forbidden.** Every Vertical Slice includes its own automated tests executed via TDD. |
| "I will paste entire file code inside the ticket description." | **Forbidden.** Use Context Pointers (file path and line ranges) and typed signatures only. |
| "I will rename the column and update 200 usages in a single ticket." | **Forbidden.** Use expand–contract. Each migration batch turns green on its own. |
| "I do not need to map shared files between tickets." | **Forbidden.** Without mapping Shared Mutable State, parallel implementations create merge conflicts. |

## 5. Execution Proof (Exit Criteria)
- `[measured]` 4-item *Throughput Checkpoint* present in the plan.
- `[measured]` Zero horizontal or test-only tickets, except expand–contract migration batches.
- `[measured]` Tickets recorded in the designated destination with explicit blocking edges.
