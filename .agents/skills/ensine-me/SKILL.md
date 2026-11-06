---
name: ensine-me
description: Teaches a topic or explains repository code via short, self-contained HTML lessons in ASD-STE100 English with cited sources. Asks beforehand whether lessons go to a local file or artifact. Use when the user types /ensine-me.
argument-hint: "What do you want to learn?"
disable-model-invocation: true
---

# Teach in Short Lessons (`/ensine-me`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Ensure the user understands and retains a technical topic or codebase component. The teaching unit is the **Lesson**: a self-contained HTML file focusing on a single concept.

## 2. Non-Negotiable Rules
- **Write in ASD-STE100:** Active voice, one instruction per sentence, short sentences.
- **Do not teach from memory:** Ground every technical statement in verified documentation, installed dependency code, or specific commits. Mark unverified claims as `[inferred]`.
- **Teach one concept per lesson:** A lesson takes approximately 10 minutes to consume and delivers concrete understanding.
- **Do not alter production code:** Read code and write only to designated lesson directories.
