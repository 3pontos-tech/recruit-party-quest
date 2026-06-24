# Module Architecture

This monorepo uses `internachi/modular`. Each module lives under
`app-modules/{kebab-case}/`, is published as `he4rt/{slug}`, and uses the namespace
`He4rt\{PascalCase}\`.

> The namespace does **not** always match the directory name — always confirm in the
> module's `composer.json`. The `panel-*` modules drop the `panel-` prefix
> (`panel-admin` → `He4rt\Admin`, not `He4rt\PanelAdmin`), and `he4rt` → `He4rt\Core`
> (a UI kit, **not** the application core — see the table below).

## Module types

| Type             | Modules                                                                 | Contains                                            |
| ---------------- | ----------------------------------------------------------------------- | --------------------------------------------------- |
| **Domain**       | `applications`, `candidates`, `recruitment`, `screening`, `feedback`, `teams`, `users`, `permissions`, `location`, `links`, `term` | Business logic: Models, Actions, DTOs, Enums, Policies |
| **Presentation** | `panel-admin`, `panel-app`, `panel-organization`                        | UI: Filament Resources/Pages/Widgets, Livewire, Blade |
| **UI kit**       | `he4rt` (`He4rt\Core`)                                                  | Shared Blade view components, CSS (incl. the `3pontos` theme) and fonts. Its `src/` holds only the ServiceProvider — no business logic. **Not** the application core, despite the name. |
| **Integration**  | _(none yet)_                                                            | Reserved for external-API modules (transport, OAuth, ETL). Add `integration-*` if one appears. |

Presentation modules own UI concerns only. Domain logic belongs in domain modules —
see the `presentation/core` guideline. The `ai` module is parked — confirm before touching it.

> Truly cross-cutting application code (the kernel) lives in the root `App\` namespace —
> **not** in a module, and in particular **not** in `he4rt`/`He4rt\Core` (a historical
> name, not a kernel). See the living decoupling analysis at the repo root
> (`ANALISE-DESACOPLAMENTO-MODULOS.md`) for confirmed boundaries, cycles, and where each
> shared concern belongs.

## Directory → namespace map

| Directory             | Namespace            | Type           |
| --------------------- | -------------------- | -------------- |
| `panel-admin`         | `He4rt\Admin`        | Presentation   |
| `panel-app`           | `He4rt\App`          | Presentation   |
| `panel-organization`  | `He4rt\Organization` | Presentation   |
| `he4rt`               | `He4rt\Core`         | UI kit         |
| `applications`        | `He4rt\Applications` | Domain         |
| `candidates`          | `He4rt\Candidates`   | Domain         |
| `recruitment`         | `He4rt\Recruitment`  | Domain         |
| `screening`           | `He4rt\Screening`    | Domain         |
| `feedback`            | `He4rt\Feedback`     | Domain         |
| `teams`               | `He4rt\Teams`        | Domain (tenancy boundary) |
| `users`               | `He4rt\Users`        | Domain         |
| `permissions`         | `He4rt\Permissions`  | Domain         |
| `location`            | `He4rt\Location`     | Domain         |
| `links`               | `He4rt\Links`        | Domain         |
| `term`                | `He4rt\Term`         | Domain         |
| `plugin-seo`          | `He4rt\PluginSeo`    | Support (SEO)  |
| `ai`                  | `He4rt\Ai`           | Domain (parked) |

## Canonical structure

```
app-modules/{module}/
├── composer.json
├── phpstan.neon
├── config/{module}.php                       (optional)
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── lang/{en,pt_BR}/                          (optional — see the modular guideline)
├── routes/{topic}-routes.php                 (optional, auto-discovered)
├── resources/views/                          (optional, presentation only)
├── src/
│   ├── {ModuleName}ServiceProvider.php       <- prefer src/ root (a few use src/Providers/)
│   ├── Actions/
│   ├── Models/
│   ├── DTOs/
│   ├── Enums/
│   ├── Exceptions/
│   ├── Policies/
│   └── ...
└── tests/
    ├── Feature/
    └── Unit/
```

## Sub-namespace strategies

**Flat layers** — simple modules (`screening`, `feedback`, `term`):
`src/Actions/`, `src/Models/`, `src/DTOs/`, `src/Enums/`.

**Sub-domain grouping** — complex modules (`recruitment`, `applications`):
`src/{SubDomain}/Actions/`, `src/{SubDomain}/Models/`. Check a sibling module before
introducing a new layout.

## ServiceProvider

Prefer `src/{ModuleName}ServiceProvider.php` — the dominant convention here (13 of 17
modules). A few modules (`ai`, `he4rt`, `links`, `term`) keep it in `src/Providers/`;
match the sibling you're editing, but place it at the `src/` root for new modules.

@verbatim
<code-snippet name="Module ServiceProvider" lang="php">
namespace He4rt\{ModuleName};

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class {ModuleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/{module}.php', '{module}');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', '{module-slug}');

        Relation::morphMap([
            'some_class' => SomeClass::class,
        ]);
    }
}
</code-snippet>
@endverbatim

Note: the actual Filament `PanelProvider`s live in `app/Providers/Filament/` (the `App\`
namespace), not in the `panel-*` module ServiceProviders — those do supporting setup only.

## Module composer.json

@verbatim
<code-snippet name="Module composer.json" lang="json">
{
    "name": "he4rt/{module-slug}",
    "autoload": {
        "psr-4": {
            "He4rt\\{Namespace}\\": "src/",
            "He4rt\\{Namespace}\\Database\\Factories\\": "database/factories/",
            "He4rt\\{Namespace}\\Database\\Seeders\\": "database/seeders/"
        }
    }
}
</code-snippet>
@endverbatim

## Dependency rules

- **Domain** modules never import from Presentation.
- **Presentation** imports from Domain; Domain never imports from Presentation.
- The `he4rt` (`He4rt\Core`) UI-kit module is imported by Presentation for view
  components and styles — it must not accumulate business logic (despite the `Core` name).
- Wrap Domain Actions inside Filament Actions at the presentation boundary (see the
  `presentation/core` guideline).
