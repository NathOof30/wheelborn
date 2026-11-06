---
name: laravel-verification
description: Verifies Laravel changes with rapid feedback during editing and a complete verification gate prior to commit, PR, or delivery. Includes Blade in Pint, mutation and CRAP testing on altered scope, browser testing, and test probes when available. Use also when recording evidence for a pause.
---

# Proportional Laravel Verification (`laravel-verification`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Prepare the Project
Read manifests, lockfiles, existing tests, and scripts in the chosen scope. `composer.lock`, `tests/Pest.php`, and `phpunit.xml` are references for PHP projects; do not invent missing files or tools. Respect the existing execution environment (e.g., Sail, native PHP). Check `./vendor/bin/pint --help` and test runner options before running conditional checks.

To enable Blade formatting in Pint, merge this into the `rules` object of the project's `pint.json`, preserving other rules:

```json
{
    "rules": {
        "Pint/laravel_blade": true
    }
}
```

This configuration requires Pint with Blade support and its Node/Prettier dependencies. Alternatively, run Pint with `--blade`. If the version does not support Blade, use the configured Blade formatter or record the blocker; never claim non-existent coverage. Do not upgrade incompatible dependencies as part of a verification step.

## 2. Choose the Scope
- **During editing:** Run the test of the affected Seam or Boundary. In PHP/Blade, run `./vendor/bin/pint --dirty --test`. Run focused static analysis according to project scripts; expand if shared contracts change.
- **Before commit, PR, or delivery:** Run the full verification gate below. A development slice may include multiple edits without repeating the entire test suite between each edit.
- **Documentation only:** Validate links, examples, and markdown rules; mark PHP tools as `not applicable`. If changes affect executable configuration, run affected checkers.
- **When pausing:** Record the latest result, its scope, and verified state. Reuse evidence if covered files and dependencies have not changed. Otherwise, mark as `stale` and leave the command pending upon resumption. Do not run the full suite solely to pause.

## 3. Full Verification Gate
This section is the single source of truth for gate order. Other skills point here.

Use the full commands configured for the application in the registered execution directory. With Blade enabled, the execution order is: formatting → static analysis → tests → test quality → browser (when section 4 requires it):

```bash
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=2G
php artisan test
```

If Pint fails, format the changed scope and repeat checks invalidated by the edit. Tests and static analysis that already passed remain valid while code, configuration, and relevant dependencies stay identical. Do not repeat checks solely to transition between phases.

Never weaken tests, types, or configuration to obtain a green result. If a tool cannot run, record the command, the error, and the missing coverage. Do not mark the full gate as passed.

### Test Quality
A green test proves only that it passes. Mutation testing proves that the test fails when domain rules break. Run this step after green tests, only on production PHP files in the diff. Both measures require code coverage drivers (PCOV or Xdebug). Without a driver or tool, record `unverified` with the reason. This absence does not block commit; it appears in the evidence and PR. Do not install dependencies solely for this step.

1. **Mutation:** Use the configured command. If unconfigured, check help:
   - Pest with `--mutate`: `./vendor/bin/pest --mutate --path=<files_in_diff_comma_separated>`.
   - Infection: `./vendor/bin/infection --git-diff-filter=AM --git-diff-base=<base>`.

   Address each surviving mutant and uncovered line:
   - Write a test that kills the mutant.
   - Or delete dead code that no behavior uses.
   - Or record the mutant as equivalent with justification. An equivalent mutant alters no observable result.

2. **CRAP (Change Risk Anti-Patterns):** Generate Clover coverage report outside repository: `php artisan test --coverage-clover=<file>`. Then run `php <skill-folder>/scripts/crap.php <file> <diff-files>`. Threshold is 10. The script exits with 1 when a method has a CRAP score of 10 or higher.
   - $\text{CRAP} = \text{CC}^2 \times (1 - \text{coverage})^3 + \text{CC}$. CC is cyclomatic complexity. With 100% coverage, $\text{CRAP} = \text{CC}$.
   - First, test uncovered branches. If CRAP remains high, decompose the method by responsibility.
   - A `match` or `switch` with simple arms may legitimately exceed the limit. Record the reason.

## 4. Proving Real Behavior
When the diff touches Blade, Livewire, Alpine, CSS, or JavaScript, prove the real path in the browser and the expected visual effect. `Livewire::test()` verifies the server boundary; it does not execute browser runtime.

- **Permanent regression tests:** Use tests in `tests/Feature` or `tests/Browser`.
- **Browser verification:** When available, drive the app with browser automation, record actions, results, console output, and persisted state.
- **Without browser:** Record the UI surface as `unverified` and explain the limitation. Do not convert server evidence into claimed UI evidence.

## 5. Output Evidence
Report command, scope, result, verified state (commit or files/diff), and artifact paths.

- `[measured]`: Executed on covered state; reusable while that state has not changed.
- `[inferred]` or `[guess]`: Conclusion reached without execution; explain basis and limit.
- `stale`, `unverified`, and `not applicable`: Use explicitly when fitting.

State whether results came from focused tests or the full suite. For UI, state the exercised flow and proof of effect.
