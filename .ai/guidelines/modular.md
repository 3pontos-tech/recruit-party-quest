## Architecture

**RPQ** (Recruit Party Quest) is a multi-tenant recruitment platform built with Laravel 12 using **Domain-Driven Design (DDD)** and a **modular monolith** architecture. Each domain is self-contained in `app-modules/` with its own models, migrations, tests, and service providers.

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

### Internationalization (i18n)

All user-facing labels and messages must be translated in **English** and **Portuguese (Brazil)**.

#### Language File Structure

Translation files live in `app-modules/{module}/lang/` with this structure:

```
app-modules/{module}/lang/
├── en/
│   ├── filament.php         # Filament UI (forms, tables, resources)
│   ├── enums.php            # Enum labels
│   ├── labels.php           # Generic field/entity labels
│   └── [context].php        # Context-specific translations
└── pt_BR/
    ├── filament.php
    ├── enums.php
    ├── labels.php
    └── [context].php
```

#### Registering Module Translations

Each module's `ServiceProvider` must load translations in the `boot()` method:

```php
public function boot(): void
{
    $this->loadTranslationsFrom(__DIR__.'/../lang', 'module-name');
}
```

The second argument is the translation **namespace** (use kebab-case, matching the module folder name).

#### Translation Key Naming Convention

Follow this pattern: `namespace::category.subcategory.key`

**Examples:**

- `users::labels.id` - User ID label
- `recruitment::filament.requisitions.title` - Recruitment resource title
- `applications::enums.application_status.new.label` - Enum label
- `screening::question_validations.required` - Validation message
- `panel-app::pages/onboarding.steps.cv.fields.cv_file` - Page-specific translations

#### Usage in Code

**In Controllers/Services:**

```php
__('module::key.subkey')
```

**In Filament Forms & Tables:**

```php
TextInput::make('name')
    ->label(__('users::labels.name')),

TextColumn::make('status')
    ->label(__('applications::filament.columns.status'))
```

**In Blade Views:**

```blade
{{ __('teams::filament.emails.team_invitation.subject', ['team_name' => $team->name]) }}
```

**In Livewire Components:**

```php
->helperText(__('panel-app::pages/onboarding.steps.cv.fields.helper'))
```

**In Enum Classes:**

```php
enum ApplicationStatus implements HasLabel
{
    case New = 'new';

    public function getLabel(): string
    {
        return __('applications::enums.application_status.'.$this->value.'.label');
    }
}
```

#### Translation File Organization

**filament.php** - Filament resources (labels, sections, actions, table columns)

```php
return [
    'resources' => [
        'user' => [
            'label' => 'User',
            'plural_label' => 'Users',
        ],
    ],
];
```

**enums.php** - Enum values with labels

```php
return [
    'application_status' => [
        'new' => ['label' => 'New'],
        'in_review' => ['label' => 'In Review'],
    ],
];
```

**labels.php** - Generic field labels

```php
return [
    'id' => 'ID',
    'name' => 'Name',
    'email' => 'Email',
    'created_at' => 'Created At',
];
```

#### Supported Locales

- **en** - English (default)
- **pt_BR** - Portuguese (Brazil)

Configure in `.env`:

```
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

#### Best Practices

1. **Always translate user-facing text** - Labels, buttons, messages, validation errors
2. **Use meaningful keys** - Avoid single-letter or unclear names
3. **Keep translations organized** - Use consistent file names and directory structures
4. **Avoid inline strings** - Never hardcode English/Portuguese strings in code
5. **Use parameters for dynamic content** - Pass variables to translations rather than concatenating strings
6. **Test both languages** - Verify translations work correctly in both locales
7. **Group related translations** - Keep similar translations in the same file

### Important Conventions

#### Namespacing

- All application code (outside modules) lives in `App\` namespace
- Module code lives in `He4rt\{ModuleName}` namespace

#### Database Relationships

- Use Eloquent relationship methods with return type hints
- Eager load relationships to prevent N+1 queries: `->with(['team', 'users'])`
- Prefer `Model::query()` over raw `DB::` queries
- Scoping: Always scope queries to the current tenant via `InteractsWithTenants`
