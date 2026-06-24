# Internationalization (i18n)

All user-facing labels and messages must be translated in **English** (`en`, default) and
**Portuguese Brazil** (`pt_BR`). Never hardcode user-facing strings — always go through `__()`.

## Language file structure

Translation files live in `app-modules/{module}/lang/`:

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

## Registering module translations

Each module's `ServiceProvider` loads its translations in `boot()`:

```php
public function boot(): void
{
    $this->loadTranslationsFrom(__DIR__.'/../lang', 'module-name');
}
```

The second argument is the translation **namespace** — kebab-case, matching the module folder name.

## Translation key naming convention

Follow `namespace::category.subcategory.key`:

- `users::labels.id` — User ID label
- `recruitment::filament.requisitions.title` — resource title
- `applications::enums.application_status.new.label` — enum label
- `screening::question_validations.required` — validation message
- `panel-app::pages/onboarding.steps.cv.fields.cv_file` — page-specific

## Usage in code

**Controllers / Actions:**

```php
__('module::key.subkey')
```

**Filament forms & tables:**

```php
TextInput::make('name')->label(__('users::labels.name'));

TextColumn::make('status')->label(__('applications::filament.columns.status'));
```

**Blade views:**

@verbatim
```blade
{{ __('teams::filament.emails.team_invitation.subject', ['team_name' => $team->name]) }}
```
@endverbatim

**Livewire components:**

```php
->helperText(__('panel-app::pages/onboarding.steps.cv.fields.helper'))
```

**Enum classes** — return the translation from the `HasLabel` interface, never a hardcoded string:

```php
enum ApplicationStatus: string implements HasLabel
{
    case New = 'new';

    public function getLabel(): string
    {
        return __('applications::enums.application_status.'.$this->value.'.label');
    }
}
```

## Translation file organization

**filament.php** — Filament resources (labels, sections, actions, columns):

```php
return [
    'resources' => [
        'user' => ['label' => 'User', 'plural_label' => 'Users'],
    ],
];
```

**enums.php** — enum values with labels:

```php
return [
    'application_status' => [
        'new' => ['label' => 'New'],
        'in_review' => ['label' => 'In Review'],
    ],
];
```

**labels.php** — generic field labels:

```php
return [
    'id' => 'ID',
    'name' => 'Name',
    'created_at' => 'Created At',
];
```

## Supported locales

- **en** — English (default)
- **pt_BR** — Portuguese (Brazil)

Configured in `.env` (`APP_LOCALE`, `APP_FALLBACK_LOCALE`).

## Best practices

1. **Always translate user-facing text** — labels, buttons, messages, validation errors.
2. **Use meaningful keys** — avoid single-letter or unclear names.
3. **Never hardcode strings** — both `en` and `pt_BR` must be kept in sync.
4. **Use parameters for dynamic content** — pass variables (`['name' => …]`) instead of concatenating.
5. **Group related translations** — keep similar keys in the same file (`filament.php`, `enums.php`, `labels.php`).
6. **Back enum labels with translations** via `HasLabel`/`HasColor`/`HasIcon`, not Filament inline strings.
