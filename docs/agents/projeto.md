# WHEELBORN Project Configuration

Default configuration consumed by agents and skills (`.agents/skills/`).

## 1. Application Details
- **Name:** WHEELBORN (RNGboy)
- **Framework:** Laravel 11.x / 12.x
- **Frontend / Reactivity:** Livewire 3/4, Alpine.js, Tailwind CSS
- **Database:** SQLite (`database/database.sqlite`)
- **PHP Version:** ^8.3 (Windows PowerShell / POSIX)

## 2. Verification Gate Commands (`laravel-verification`)
- **Code Formatting Check:**
  ```bash
  ./vendor/bin/pint --test
  ```
- **Code Formatting Fix:**
  ```bash
  ./vendor/bin/pint
  ```
- **Automated Tests:**
  ```bash
  php artisan test
  ```
- **Filtered Tests:**
  ```bash
  php artisan test --filter=<TestName>
  ```
- **Test Environment:** SQLite in-memory or local test database (`phpunit.xml`)

## 3. Project File Destinations
| Resource | Destination in Repository |
| :--- | :--- |
| **Tickets To Do** | `tickets/todo/` |
| **Tickets Done** | `tickets/done/` |
| **Specifications & GDD** | `docs/` |
| **Feature Map** | `docs/features/` |
| **Test Evidence** | `docs/evidencias/` |

## 4. Architectural Conventions (`laravel-deep-modules`)
- Every production PHP file starts with `declare(strict_types=1);`.
- State properties or sensitive IDs in Livewire components use `#[Locked]`.
- Mutations of inventory, coins, charges, and battles use `DB::transaction()` with `lockForUpdate()`.
- Internal code, classes, methods, and tests are written in English; player-facing text is written in French.
