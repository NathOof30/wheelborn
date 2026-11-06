---
name: laravel-deep-modules
description: Deep Modules architectural discipline for PHP, Laravel, and Livewire. Use when designing, planning, implementing, or reviewing Actions, DTOs, Models, Controllers, and Livewire components.
---

# Deep Modules in Laravel and Livewire (`laravel-deep-modules`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Encapsulate business rules behind small, testable interfaces. Prefer existing public Seams and keep validation and authorization explicit at the Boundary.

## 2. Non-Negotiable Rules
- **Declare strict types in PHP code:** Start PHP files with `declare(strict_types=1);`. Blade templates (`.blade.php`) are exempt.
- **Preserve existing architecture:** Before creating classes, identify the public Seam and the complexity it encapsulates. Do not refactor neighboring code merely to enforce this pattern.
- **Keep the Boundary thin:** Validate input and authorization at the Boundary. Extract rules, transactions, and coordination when complexity exists. Trivial CRUD may remain in the Controller or Livewire component if it aligns with project conventions and remains testable.
- **Protect Livewire public state:** Add the `#[Locked]` attribute to any Livewire component public property storing IDs or state that the client must not manipulate.
- **Select data by contract:** Prefer `final readonly` DTOs when multiple fields, invariants, or consumers require an explicit contract. Reuse existing types; scalar values or local validated data are sufficient for simple operations. Do not create pass-through DTOs without real benefit.
- **Design small interfaces:** For a new Action, prefer `handle()` and encapsulate the relevant workflow. Preserve already established public signatures. Justify design choices or exceptions in the ticket/Spec in one sentence; do not create an ADR solely for this.
- **Make invalid state unrepresentable:** Use `enum`, `readonly` DTO, and strict types so PHPStan rejects invalid values. Never silence PHPStan with `@phpstan-ignore` or new baseline entries.
- **Validate once, at entry:** `FormRequest` or `Livewire\Form` validates and converts input into types or DTO. Inside the Seam, trust the data. Do not repeat validation or sprinkle defensive `if ($x !== null)` checks.
- **Make operations repeatable:** Retried jobs, webhooks, scheduled commands, and Livewire double-clicks execute the same operation again. Running twice, or resuming after a mid-execution failure, must leave the exact same final state. Use unique indexes with `upsert`, idempotency keys, `ShouldBeUnique`, or state checks inside transactions.
- **Enforce a single writer on shared state:** If two processes do not need the same mutable record, isolate them. If they do, enforce a single writer: `lockForUpdate()` in transaction, atomic `increment()`, or jobs with `WithoutOverlapping`.

## 3. Action Example with Typed Contract

```php
<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\OrderStatus;

final readonly class ApproveOrderData
{
    public function __construct(
        public int $orderId,
        public int $approverId,
        public OrderStatus $status,
    ) {}
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Data\ApproveOrderData;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final readonly class ApproveOrderAction
{
    public function handle(ApproveOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = Order::query()->lockForUpdate()->findOrFail($data->orderId);
            $order->update([
                'status' => $data->status,
                'approved_by' => $data->approverId,
            ]);

            return $order;
        });
    }
}
```

## 4. Forbidden Rationalizations (Anti-Shortcuts)
| Agent Excuse | Required Rule |
|---|---|
| "The Eloquent query is only 4 lines, I will put it directly in the Livewire `save()` method." | Evaluate behavior: trivial CRUD may stay at the Boundary; rules, transactions, and coordination require a dedicated Seam. Line count alone does not decide. |
| "I will pass `$request->validated()` as an `array` directly to the Action." | Prefer DTO when stabilizing a contract between modules. Do not add a class merely to forward local validated data. |
| "I will create an interface `ApproveOrderActionInterface` even though there is only one implementation." | **Forbidden (Anti-Shallow Module).** Do not create pass-through interfaces without an actual need for multiple implementations. |
| "The job has `$tries = 3`; if it fails, it will just re-run." | **Forbidden.** Retries repeat writes that already succeeded. Prove that a 2nd execution leaves the exact same state. |
| "PHPStan complains about the type; I will add `@phpstan-ignore` and move on." | **Forbidden.** The error reveals a possible invalid state. Fix the type or data origin. |

## 5. Execution Proof (Exit Criteria)
When modifying PHP code, apply `laravel-verification`. In design or documentation tasks, record decisions without running PHP tools merely because this skill is loaded.
- `[measured]` Created or edited PHP files (except `.blade.php` templates) contain `declare(strict_types=1);`.
- `[measured]` Selected Seam and encapsulated complexity identified; exceptions explained when applicable.
- `[measured]` Zero errors reported by static analysis without `@phpstan-ignore` or baseline additions.
- `[measured]` For each repeatable operation in the diff (job, webhook, scheduled command, or mutating Livewire action): a test executes it twice and asserts the same final state.
