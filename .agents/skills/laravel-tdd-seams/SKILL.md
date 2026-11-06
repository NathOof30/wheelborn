---
name: laravel-tdd-seams
description: Test-Driven Development (TDD) discipline with Pest/PHPUnit focused on public Action Seams, Livewire components, and Architecture Tests. Use when writing or reviewing tests in Laravel projects, and when two consecutive fixes fail on the same test or gate.
---

# Seam TDD with Pest & PHPUnit (`laravel-tdd-seams`)

## Project Configuration
Read the configuration specified in the project instructions; if missing, look for `docs/agents/projeto.md` at the repository root. Reuse it while unchanged. Use its paths, commands, and scope; the examples below are fallbacks. The session takes precedence. If unconfigured, ask only for missing required decisions and offer `/setup` once, without blocking the task.

## 1. Goal
Ensure that all application behavior is driven by tests focused on public Seams (`Actions` and `Livewire`/HTTP Boundaries), strictly forbidding fragile mocks of internal details.

## 2. Non-Negotiable Rules
- **Test behavior, never implementation:** Assert the output of the chosen Seam, the state persisted in the database, and the response of the `Livewire` component or controller.
- **Delete tests that pass with an empty Seam:** Imagine the Seam returning `null` or an empty collection without writing to the database. If the test still passes, it proves nothing. Rewrite it to assert visible value and state.
- **Do not write false tests:** Sometimes an honest automated test is impossible (concurrency race condition, load, external service without fake). Do not write a test that cannot fail. Document the reason and use the closest verifiable proof, such as query count assertions or schema unique constraints. Mark this proof as `partial`; without proof, mark `unverified`. This does not exempt other behaviors from RED-GREEN TDD.
- **Attack the premise:** When two consecutive fixes based on the same premise fail on the same test or gate: stop and revert both. State that premise in a single sentence. Prove or refute the premise with a concrete execution (a test or query). Do not start a 3rd fix before obtaining proof. If the premise is refuted, revisit hypotheses.
- **Forbid internal Eloquent mocks:** Never mock `Builder`, `Model`, or internal repositories. Use `RefreshDatabase` (or `lazilyRefreshDatabase`) with real Laravel Factories.
- **Mock only external boundaries:** Use `Http::fake()`, `Bus::fake()`, `Queue::fake()`, `Mail::fake()`, or `Storage::fake()` exclusively to isolate network and external I/O.
- **Test both the Seam and the Boundary:**
  1. Test domain rules through the chosen public Seam (Action or existing service). Trivial CRUD can be covered at the Boundary without inventing an Action/DTO solely for the test.
  2. Test authorization, validation, and integration at the Boundary: `Livewire::test()` for Livewire (including `#[Locked]`) or HTTP tests for Controllers. Prove UI interactions in browser when applicable.
- **Protect architecture with Arch Tests:** Keep architecture tests in `tests/ArchTest.php` that enforce structural constraints.

## 3. Required Testing Pattern

```php
<?php

declare(strict_types=1);

use App\Actions\Orders\ApproveOrderAction;
use App\Data\ApproveOrderData;
use App\Enums\OrderStatus;
use App\Livewire\Orders\ApproveOrderModal;
use App\Models\Order;
use App\Models\User;
use Livewire\Livewire;

it('approves an order at the Action seam', function (): void {
    $approver = User::factory()->create();
    $order = Order::factory()->pending()->create();

    $action = app(ApproveOrderAction::class);
    $updated = $action->handle(new ApproveOrderData(
        orderId: $order->id,
        approverId: $approver->id,
        status: OrderStatus::Approved,
    ));

    expect($updated->status)->toBe(OrderStatus::Approved)
        ->and($updated->approved_by)->toBe($approver->id);
});

it('blocks unauthorized users at the Livewire boundary', function (): void {
    $unauthorizedUser = User::factory()->create();
    $order = Order::factory()->pending()->create();

    Livewire::actingAs($unauthorizedUser)
        ->test(ApproveOrderModal::class, ['orderId' => $order->id])
        ->call('approve')
        ->assertForbidden();
});
```

### Example Architecture Test (`tests/ArchTest.php`)
```php
<?php

declare(strict_types=1);

arch('PHP files use strict_types')
    ->expect('App')
    ->toUseStrictTypes();

arch('DTOs are final and readonly')
    ->expect('App\Data')
    ->toBeFinal()
    ->toBeReadonly();

arch('production code contains no debug helpers')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();
```

## 4. Forbidden Rationalizations (Anti-Shortcuts)
| Agent Excuse | Required Rule |
|---|---|
| "I will use `Mockery::mock(Order::class)` so the test runs without a database." | **Forbidden.** Eloquent mocks lie about SQL schema, constraints, and relationships. Use Factories + `RefreshDatabase`. |
| "I will use `ReflectionClass` to test a `private` method in the Action." | **Forbidden.** Private methods are implementation details. Test the public interface. |
| "I will write production code first and add tests afterward." | **Forbidden.** Follow the RED → GREEN → REFACTOR cycle strictly. |
| "`expect($order)->not->toBeNull()` is enough to cover the Action." | **Forbidden.** This test passes on almost any return value. Assert status, values, and persisted database records. |
| "I cannot reproduce the race condition easily; I will write a dummy test that just calls the method." | **Forbidden.** A test that cannot fail hides gaps. Record the reason and use the closest partial proof. |
| "The 2nd fix also failed; let me try a 3rd variation." | **Forbidden.** Two failures on the same premise indicate a false premise. State the premise, prove or refute it before continuing. |

## 5. Execution Proof (Exit Criteria)
- `[measured]` Test observed failing (`RED`) before implementation and passing (`GREEN`) afterward via test runner (`php artisan test` or `./vendor/bin/pest`).
- `[measured]` After 2 failed attempts with the same premise: the premise stated in one sentence and the execution that proves or refutes it.
