# Recruit Party Quest — Project Context

Recruit Party Quest (RPQ) is a **multi-tenant recruitment platform**. Organizations run
their hiring pipelines — job requisitions and postings, candidate applications, screening,
feedback and offers — through dedicated Filament panels.

**Target audience:** recruiting organizations (the tenants) and the candidates who apply to
their openings.
**Channels:** Web only — three Filament panels (admin, app, organization).

## Modular Monolith (`internachi/modular`)

Self-contained domain modules following **Domain-Driven Design (DDD)** — each owns its
models, migrations, tests and service provider.

| Layer            | Modules                                                                 |
| ---------------- | ----------------------------------------------------------------------- |
| **Domain**       | `applications`, `candidates`, `recruitment`, `screening`, `feedback`, `teams`, `users`, `permissions`, `location`, `links`, `term` |
| **Presentation** | `panel-admin`, `panel-app`, `panel-organization`                        |
| **UI kit**       | `he4rt` (`He4rt\Core` — shared Blade components, CSS and fonts)          |

See the `module-architecture` guideline for the full layout, the directory→namespace map,
and dependency rules. The `ai` module is parked — confirm before touching it.

## Key Conventions

- **Tenant = `Team`** (organization) — Filament-native tenancy, slug in the URL; only the
  organization panel is tenant-scoped. See `multi-tenancy`.
- **Namespace:** `He4rt\{Module}` (exceptions: `panel-*` → `He4rt\{Name}`, `he4rt` → `He4rt\Core`).
- **Shared / kernel code** lives in `App\`, **not** in a module — see `module-architecture`.
- **Language:** PT-BR for user-facing strings, English for code. The i18n rules live in `i18n`.
- **PHP:** 8.4. **Filament:** v5. **Livewire:** v4. **DB:** PostgreSQL.
- **Testing:** Pest v4. **Quality:** Larastan (modular `phpstan.neon` per module), Rector, Pint.
