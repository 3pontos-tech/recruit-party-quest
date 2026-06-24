@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp

# PHPStan / Larastan — ignoreErrors Conventions

RPQ uses Larastan v3. The root `phpstan.neon` and each module's `phpstan.neon` may carry
suppressions. When adding entries to `ignoreErrors` in **any** `phpstan.neon`, always use the
**indented block style**: a lone `-` on its own line, with keys indented beneath it. Never use the
inline `- { … }` style — it requires horizontal scrolling and hurts readability.

## Correct format

@verbatim
<code-snippet name="ignoreErrors block style" lang="neon">
parameters:
    ignoreErrors:
        -
            message: '#^Error message regex here#'
            identifier: error.identifier
            count: 1
            path: src/Path/To/File.php
</code-snippet>
@endverbatim

## Rules

- `message` must be a regex wrapped in `#` delimiters.
- Always scope errors to a specific `path` — never leave an entry without one.
- Always include `count` so PHPStan warns if the number of occurrences changes.
- Always include `identifier` when PHPStan provides one (e.g. `property.notFound`).
- Do not escape spaces with `\ ` inside `#…#` regex patterns.
- Prefer fixing the root cause over ignoring. Only ignore when:
    - The error comes from third-party or generated code.
    - It's a known PHPStan/Larastan limitation (e.g. Livewire `$form`, Filament breezy custom
      profile page, non-null-typed `HasOne` access — see the project memory for documented cases).

## Baseline

Prefer `{{ $assist->binCommand('phpstan analyse --generate-baseline') }}` for bulk legacy errors.
Manual `ignoreErrors` entries are reserved for intentional, documented suppressions only.
