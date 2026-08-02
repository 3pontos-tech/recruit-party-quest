@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

# Model PHPDoc Sync — Mandatory on Schema Changes

**Priority: HIGH** — This rule is non-negotiable. Every schema change MUST update the
corresponding model PHPDoc. This is already the prevailing practice here — most models carry
`@property` blocks and use `#[UseFactory]`.

## Rule

When you **add, remove, rename, or change the type** of any database column (via migration,
manual SQL, or schema dump), the `@property` PHPDoc block on the affected Model class
**MUST be updated in the same commit**.

## What triggers this rule

- `{{ $assist->artisanCommand('make:migration') }}` that adds/removes/alters columns
- Any edit to an existing migration file
- Any raw SQL that changes table structure
- Renaming a column or changing its type, nullability, or a default that changes the PHPDoc type

## PHPDoc format

@verbatim
<code-snippet name="Model PHPDoc block" lang="php">
/**
 * @property string $id
 * @property string $team_id
 * @property string $name
 * @property string|null $description
 * @property bool $active
 * @property ApplicationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UseFactory(ExampleFactory::class)]
final class Example extends Model
</code-snippet>
@endverbatim

## Type mapping

| Column type              | PHPDoc type                  |
| ------------------------ | ---------------------------- |
| `uuid`, `string`, `text` | `string`                     |
| `integer`, `bigInteger`  | `int`                        |
| `boolean`                | `bool`                       |
| `timestamp`, `datetime`  | `Carbon\|null`               |
| `json`, `jsonb`          | `array<string, mixed>\|null` |
| `decimal`, `float`       | `float`                      |
| `enum` (backed cast)     | `EnumClass`                  |

- Add `|null` when the column is nullable.
- Use the **cast type** for enums and custom casts, not the raw DB type.
- Primary keys are string-typed (`id` is `string`).
- `created_at` / `updated_at` are always `Carbon|null`.

## Explicit class-level attributes

Declare this attribute explicitly, even when the value matches Laravel's convention. This
is the standard for new and refactored models:

- `#[UseFactory(XxxFactory::class)]` — explicit factory binding, instead of a `newFactory()`
  override. The `HasFactory` trait is still required (it provides `factory()`).

Set the table name with the `protected $table` property. There is **no** `#[Table]` attribute
in this Laravel version — `Illuminate\Database\Eloquent\Attributes\` ships `UseFactory`,
`UsePolicy`, `ObservedBy`, `ScopedBy` and friends, but not `Table`. Declaring it makes the
model fall back to the conventional table name and breaks every query against it.

@verbatim
<code-snippet name="Explicit model attributes" lang="php">
use Illuminate\Database\Eloquent\Attributes\UseFactory;

#[UseFactory(UserFactory::class)]
final class User extends Model
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $table = 'users';
}
</code-snippet>
@endverbatim

## Verification

Before marking a schema task as done, confirm:

1. The model has a `/** @property … */` block covering **every** column in the table.
2. Types match the column definition and any explicit `casts()`.
3. The model declares `#[UseFactory(XxxFactory::class)]`.
4. PHPStan passes (`{{ $assist->binCommand('phpstan analyse') }}`).
