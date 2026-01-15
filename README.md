# RPQ — Recruit Party Quest

**RPQ (Recruit Party Quest)** is a multi-tenant recruitment platform designed using **Domain-Driven Design (DDD)** principles.  
This repository focuses on the **core data model and domain boundaries** that support the full hiring lifecycle.

Detailed documentation for each domain lives in:

## Core Concepts

- Domain-Driven Design with explicit bounded contexts
- Multi-tenancy as a first-class concernDriven
- Clear aggregate roots and ownership
- Auditability and extensibility by design

---

## Modules Overview

RPQ is split into the following modules, aligned with the MVP features:

- **Users** — User management, models, policies, factories.
- **Permissions** — RBAC (Roles/Permissions via Spatie), policies.
- **Panel(admin)** — Admin panel with User/Role resources.
- **Panel(app)** — App with User/Role resources.
- **Organization** — tenant boundary, departments, locations
- **Recruitment** — job requisitions and public postings
- **Pipeline & Screening** — stages, interviewers, knockout logic
- **Candidates** — profiles, skills, education, work history
- **Applications** — candidate-to-requisition lifecycle
- **Evaluation & Feedback** — interviews, ratings, comments
- **Talent Pool** — reusable candidate sourcing
- **Communication** — event-driven candidate messaging (conceptual)

Each domain owns its data and exposes relationships explicitly.

> Modules prefixed with `panel-` are for **Filament-based UIs**

## Philosophy

- Business language reflected in schema
- Strong boundaries, minimal coupling
- Designed to scale in complexity without rewrites

## Modular Architecture

It follows a **modular monolith** architecture for better organization, scalability, and maintainability.

Core code lives in `app/` (standard Laravel), while business domains and UIs are separated into self-contained **modules** under `app-modules/`.

### Module Structure

```
app-modules/{module-name}/
├── src/                      # Core PHP classes
│   ├── Models/               # Eloquent models
│   ├── Policies/             # Authorization policies
│   ├── Resources/            # Filament resources (panels only)
│   ├── Schemas/              # Form/Table schemas
│   ├── Tables/               # Table definitions
│   └── {Module}ServiceProvider.php  # Module bootstrap
├── tests/
│   └── Feature/              # Pest feature/unit tests
├── database/
│   ├── factories/            # Model factories
│   └── migrations/           # Module migrations (if any)
└── config/                   # Module-specific config (e.g., rbac.php)
```

## Development Conventions

- **Namespaces**: `He4rt\{PascalCasedModule}` (e.g., `He4rt\Admin`)`.`
- **Service Providers**: One per module for auto-registration.
- **Policies**: Attached via `#[UsePolicy(...)]` attributes on models.
- **Testing**: Pest v4 feature tests per module; use factories; assertions like `assertSuccessful()`, `livewire()`.
- **Filament v4**: Use schemas, `relationship()`, Heroicons; tests with `livewire(Class::class)`.
- **PHP**: Strict types, constructor promotion, explicit types/returns.
- **Formatting**: Laravel Pint v1.
- **Analysis**: PHPStan and Rector.

## Makefile

Development workflow powered by [Makefile](Makefile). Run `make help` for all commands.

### Key Commands

| Command              | Alias | Description                                 |
|----------------------|-------|---------------------------------------------|
| `make test`          | `t`   | Run all Pest tests (`--parallel --compact`) |
| `make test-feature`  |       | Feature tests only                          |
| `make pint`          |       | Run Pint formatter                          |
| `make phpstan`       | `p`   | PHPStan analysis                            |
| `make check`         | `c`   | Dry-run: Rector/Pint/PHPStan                |
| `make format`        | `f`   | Rector + Pint fixes                         |
| `make route-list`    | `rl`  | List routes (`--except-vendor`)             |
| `make migrate-fresh` |       | Reset & seed DB                             |
| `make env-up`        |       | Docker Compose up                           |
| `make env-down`      |       | Docker down (clean)                         |
| `make dev`           |       | `composer run dev` (Vite)                   |
| `make setup`         |       | Full project setup                          |

## Quick Start

```bash
make setup          # Install deps, etc.
make env-up         # Start Docker (DB, etc.)
make migrate-fresh  # DB setup
make dev            # Frontend build/watch
```

Access admin panel (SuperAdmin required): `/admin` (create via tinker or seed).

## Additional Info

- **Docker**: `docker-compose.yml` for dev env.
- **Vite**: Tailwind v4 CSS-first config.
- **RBAC**: Spatie Permission; sync via `php artisan sync:permissions`.
- Docs: Use Laravel/Filament v4 guides.

For contributions, follow Laravel standards.
