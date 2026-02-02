## Architecture

### Modular Structure

The application uses the **Modular Monolith** pattern with clear separation of concerns:

```
app-modules/{module}/
├── src/                    # PHP classes (Models, Policies, etc.)
├── tests/                  # Pest feature/unit tests
├── database/               # Factories, migrations, seeders
└── config/                 # Module-specific config
```

**Key Modules:**

- **users**: Authentication, identity, and user profiles
- **teams**: Organizations, departments, team membership (multi-tenancy boundary)
- **permissions**: RBAC via Spatie Package
- **recruitment**: Job requisitions, postings, pipeline stages
- **applications**: Application lifecycle, stage transitions, offers
- **candidates**: Candidate profiles, skills, education, work history
- **screening**: Screening questions and knockout logic
- **feedback**: Evaluations, ratings, and comments
- **location**: Polymorphic addresses for entities
- **panel-admin**: Filament admin panel for system management
- **panel-app** / **panel-organization**: Tenant-facing UIs

**Namespace Convention:** All modules use `He4rt\{Module}` namespace (e.g., `He4rt\Users`, `He4rt\Recruitment`). They are loaded as path repositories in `composer.json`.

### Module Integration

Modules integrate via:

- **Events**: Published by models/services and consumed by other modules
- **Traits**: Shared behavior like `InteractsWithTenants`, `LogsActivity`
- **Direct Dependencies**: Composer path repositories allow direct `require` statements
- **Service Providers**: Each module registers its routes, migrations, and bindings

### Adding a New Module

The `internachi/modular` package handles module scaffolding. Create via Artisan if needed, but typically modules are already defined. Ensure the new module is added to `composer.json` repositories.

### Database Migrations

Migrations live in `app-modules/{module}/database/migrations/`. They use PostgreSQL (with Laravel PostgreSQL Enhanced for advanced features). When modifying columns, include all previous attributes or they will be lost.

### Important Conventions

#### Namespacing

- All application code (outside modules) lives in `App\` namespace
- Module code lives in `He4rt\{ModuleName}` namespace

#### Database Relationships

- Use Eloquent relationship methods with return type hints
- Eager load relationships to prevent N+1 queries: `->with(['team', 'users'])`
- Prefer `Model::query()` over raw `DB::` queries
- Scoping: Always scope queries to the current tenant via `InteractsWithTenants`
