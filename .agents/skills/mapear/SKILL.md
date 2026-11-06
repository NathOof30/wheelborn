---
name: mapear
description: Creates and maintains the application Feature Map, an index and file per Feature containing user entrypoints, recipes, and pitfalls. Updates map after changes and audits it against code without driving the app. Use when the user types /mapear.
argument-hint: "[audit] [Domain or Feature]"
disable-model-invocation: true
---

# Map Application Features (`/mapear`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Maintain a behavioral inventory of the application from the user's perspective: the **Feature Map**. An agent reads only the affected Feature File to understand valid verification procedures. Developers use the map as a regression checklist.

## 2. Non-Negotiable Rules
- **Describe behavior, not implementation:** Name screens, buttons, URLs, commands, and visible results. Keep internal classes and file paths out, except for Anchors and regression tests.
- **Cover every entrypoint:** Each Anchor in the inventory belongs to a Feature or the `Out of Scope` section with justification.
- **Do not drive the app during mapping:** Read routes, Livewire components, and console commands. Running seeders, migrations, and browsers belongs to verification skills.
- **Protect secrets:** Never copy API keys, tokens, or `.env` credentials into feature files.
