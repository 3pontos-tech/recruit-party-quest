---
module: candidates
date: 2026-08-02
authors: [Clintonrocha98]
id: candidates/plan/extracao-de-cargo-e-competencias
related: [candidates/spec/extracao-de-cargo-e-competencias]
title: 'Plano: extração de cargo e competências do currículo'
summary: 'Plano de implementação em 8 tarefas TDD para corrigir o crash na hidratação das experiências e passar a extrair cargo e competências do CV.'
format: plan
purpose: how-to
department: ti
audience: [ti]
keywords: [onboarding, extracao, curriculo, gemini, metadata, cargo, competencias]
status: published
---

# Extração de cargo e competências do currículo — Plano de Implementação

> **Para agentes:** SUB-SKILL OBRIGATÓRIA — use `superpowers:subagent-driven-development`
> (recomendado) ou `superpowers:executing-plans` para executar tarefa a tarefa.
> Os passos usam checkbox (`- [ ]`) para acompanhamento.

**Spec:** `app-modules/candidates/docs/specs/2026-08-02-extracao-de-cargo-e-competencias-do-cv.md`

**Objetivo:** Corrigir o `ErrorException: Undefined array key "company_name"` tornando o
contrato com o Gemini explícito, e passar a extrair cargo e competências do CV,
substituindo as heurísticas que hoje inventam esses dados no painel do RH.

**Arquitetura:** O schema enviado ao Gemini passa a declarar `requiredFields` e ganha
`position` e `skills`. A hidratação dos DTOs vira tolerante a campos ausentes. `position`
vira coluna; `skills` vive em `metadata`, tipado por um value object com cast dedicado. O
objeto tipado nunca cruza o Livewire — DTO e state do formulário permanecem primitivos.

**Tech Stack:** PHP 8.4, Laravel 12, Filament v5, Livewire v4, Prism (Gemini), Pest v4,
PostgreSQL, Larastan nível 7.

## Restrições globais

- Todo texto visível ao usuário passa por `__()`, com chave em `en` **e** `pt_BR`.
- Módulo `candidates` = domínio; nunca importa de `panel-*`.
- Migrations do módulo: `php artisan make:migration {nome} --module=candidates --no-interaction`.
- Model com mudança de schema exige `@property` atualizado (regra `model-phpdoc-sync`).
- `ignoreErrors` do PHPStan, se necessário, em bloco indentado com `path` e `count`.
- Rodar teste único: `php artisan test --compact --filter="<nome do teste>"`.
- Antes do push: `rector --dry-run` → `pint --test` → `phpstan analyse` →
  `nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact`.
- Nunca adicionar `Co-Authored-By` nos commits.

---

## Estrutura de arquivos

| Arquivo                                                            | Responsabilidade                   | Tarefa |
| ------------------------------------------------------------------ | ---------------------------------- | ------ |
| `src/AI/Schema/CvDataSchema.php`                                   | contrato com o Gemini              | 1      |
| `src/AI/Prompts/CvAnalysisPrompt.php`                              | instruções de extração             | 1      |
| `tests/Unit/CvDataSchemaTest.php`                                  | **novo** — verifica o JSON enviado | 1      |
| `src/DTOs/CandidateWorkExperienceDTO.php`                          | hidratação da experiência          | 2      |
| `src/DTOs/CandidateEducationDTO.php`                               | hidratação da formação             | 2      |
| `src/Actions/Onboarding/CompleteOnboardingAction.php`              | orquestra a chamada                | 2      |
| `src/DTOs/WorkExperienceMetadata.php`                              | **novo** — value object            | 3      |
| `src/Casts/AsWorkExperienceMetadata.php`                           | **novo** — cast Eloquent           | 3      |
| `tests/Unit/WorkExperienceMetadataTest.php`                        | **novo**                           | 3      |
| `database/migrations/..._add_position_...php`                      | **novo** — coluna                  | 4      |
| `src/Models/WorkExperience.php`                                    | cast, PHPDoc, `#[Table]`           | 4      |
| `database/factories/WorkExperienceFactory.php`                     | seed sem campos fantasma           | 4      |
| `src/Actions/Onboarding/StoreCandidateWorkExperiences.php`         | persistência                       | 5      |
| `panel-app/src/Filament/Pages/OnboardingWizard.php`                | formulário do wizard               | 6      |
| `panel-app/src/Livewire/MyProfile/CandidateWorkExperience.php`     | formulário do perfil               | 7      |
| `panel-organization/resources/views/.../work-experience.blade.php` | card do RH                         | 8      |

Traduções entram na tarefa que consome cada chave (6, 7 e 8).

---

## Task 1: Contrato explícito com o Gemini

**Arquivos:**

- Modificar: `app-modules/candidates/src/AI/Schema/CvDataSchema.php`
- Modificar: `app-modules/candidates/src/AI/Prompts/CvAnalysisPrompt.php`
- Criar: `app-modules/candidates/tests/Unit/CvDataSchemaTest.php`

**Interfaces:**

- Consome: nada.
- Produz: o payload do Gemini passa a conter, por experiência, as chaves
  `company_name`, `position`, `description`, `skills`, `start_date`, `end_date`,
  `is_currently_working_here` — consumido pela Task 2.

- [ ] **Passo 1: Escrever o teste que falha**

Criar `app-modules/candidates/tests/Unit/CvDataSchemaTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Candidates\AI\Schema\CvDataSchema;
use He4rt\Candidates\Enums\ResumeErrorReasons;
use Prism\Prism\Providers\Gemini\Maps\SchemaMap;

function cvSchemaArray(): array
{
    return (new SchemaMap(CvDataSchema::make(ResumeErrorReasons::NotAnCV)))->toArray();
}

it('declares the minimum required fields for a work experience', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['required'])
        ->toBe(['company_name', 'start_date', 'is_currently_working_here']);
});

it('exposes position and skills as extractable fields', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['properties'])->toHaveKeys([
        'company_name', 'position', 'description', 'skills',
        'start_date', 'end_date', 'is_currently_working_here',
    ])->and($experience['properties']['skills']['type'])->toBe('array');
});

it('keeps position and skills optional so the model never invents them', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['required'])
        ->not->toContain('position')
        ->not->toContain('skills')
        ->not->toContain('description');
});

it('requires is_cv at the root so validate() always has the flag', function (): void {
    expect(cvSchemaArray()['required'])->toContain('is_cv');
});

it('declares required fields for education', function (): void {
    $education = cvSchemaArray()['properties']['education']['items'];

    expect($education['required'])->toBe(['institution', 'start_date', 'is_enrolled']);
});
```

- [ ] **Passo 2: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="declares the minimum required fields for a work experience"
```

Esperado: FAIL com `Undefined array key "required"` — hoje o schema não declara nenhum.

- [ ] **Passo 3: Adicionar os campos e o `requiredFields` no schema**

Em `CvDataSchema::make()`, substituir o corpo por:

```php
return new ObjectSchema(
    'cv_data',
    'Dados extraídos do currículo',
    /** @phpstan-ignore-next-line argument.type */
    [
        'is_cv' => new BooleanSchema(
            'is_cv',
            'Define se o arquivo é um currículo válido.'
        ),
        'rejection_reason' => new StringSchema(
            'rejection_reason',
            sprintf('Motivo da rejeição seguindo esses padrões (ex: {%s}).', $notAnCv->value)
        ),
        'work_experiences' => new ArraySchema(
            'work_experiences',
            'Lista de experiências profissionais',
            /** @phpstan-ignore-next-line argument.type */
            new ObjectSchema(
                'experience',
                'Detalhes da experiência',
                [
                    'company_name' => new StringSchema('company_name', 'Nome da empresa'),
                    'position' => new StringSchema('position', 'Cargo ou função exercida, exatamente como aparece no currículo (ex.: Analista de RH Pleno)'),
                    'description' => new StringSchema('description', 'Descrição das atividades'),
                    'skills' => new ArraySchema(
                        'skills',
                        'Competências, tecnologias e ferramentas citadas nesta experiência',
                        new StringSchema('skill', 'Nome da competência')
                    ),
                    'start_date' => new StringSchema('start_date', 'Data de início YYYY-MM-DD'),
                    'end_date' => new StringSchema('end_date', 'Data de término YYYY-MM-DD ou null', nullable: true),
                    'is_currently_working_here' => new BooleanSchema('is_currently_working_here', 'Se trabalha lá'),
                ],
                requiredFields: ['company_name', 'start_date', 'is_currently_working_here'],
            )
        ),
        'education' => new ArraySchema(
            'education',
            'Lista de formação acadêmica',
            /** @phpstan-ignore-next-line argument.type */
            new ObjectSchema(
                'education_item',
                'Detalhes da formação',
                [
                    'institution' => new StringSchema('institution', 'Nome da instituição'),
                    'degree' => new StringSchema('degree', 'Grau acadêmico'),
                    'field_of_study' => new StringSchema('field_of_study', 'Curso'),
                    'start_date' => new StringSchema('start_date', 'Data de início YYYY-MM-DD'),
                    'end_date' => new StringSchema('end_date', 'Data de término YYYY-MM-DD', nullable: true),
                    'is_enrolled' => new BooleanSchema('is_enrolled', 'Se ainda está cursando'),
                ],
                requiredFields: ['institution', 'start_date', 'is_enrolled'],
            )
        ),
    ],
    requiredFields: ['is_cv'],
);
```

- [ ] **Passo 4: Rodar os testes do schema**

```bash
php artisan test --compact --filter=CvDataSchema
```

Esperado: 5 passando.

- [ ] **Passo 5: Atualizar o prompt**

Em `CvAnalysisPrompt::make()`, substituir a seção de extração:

```php
### EXTRAÇÃO (Se is_cv: TRUE):
- Extraia até 5 experiências profissionais e a formação acadêmica.
- Para cada experiência, extraia o cargo (position) exatamente como aparece no
  currículo. Se o cargo não estiver explícito, omita o campo — nunca deduza nem invente.
- Em skills, liste as competências, tecnologias e ferramentas citadas naquela
  experiência específica. Se nenhuma for citada, retorne uma lista vazia.
```

- [ ] **Passo 6: Rodar a suíte de onboarding para garantir que nada quebrou**

```bash
php artisan test --compact --filter=CompleteOnboardingAction
```

Esperado: tudo verde (os testes usam Prism fake; o schema não altera as asserções).

- [ ] **Passo 7: Commit**

```bash
git add app-modules/candidates/src/AI app-modules/candidates/tests/Unit/CvDataSchemaTest.php
git commit -m "fix(candidates): declare required fields and extract position/skills in cv schema"
```

---

## Task 2: Hidratação tolerante a campos ausentes

**Arquivos:**

- Modificar: `app-modules/candidates/src/DTOs/CandidateWorkExperienceDTO.php`
- Modificar: `app-modules/candidates/src/DTOs/CandidateEducationDTO.php`
- Modificar: `app-modules/candidates/src/Actions/Onboarding/CompleteOnboardingAction.php:166`
- Testar: `app-modules/candidates/tests/Feature/CandidateDTOTest.php`

**Interfaces:**

- Consome: o payload da Task 1.
- Produz: `CandidateWorkExperienceDTO` com dois campos novos —
  `public ?string $position = null` e `public array $skills = []` (lista de strings).
  `jsonSerialize()` passa a devolver as chaves `position` e `skills`. Consumido pelas
  Tasks 5, 6 e 7.

- [ ] **Passo 1: Escrever os testes que falham**

Acrescentar em `app-modules/candidates/tests/Feature/CandidateDTOTest.php`, dentro do
`describe('CandidateWorkExperienceDTO', ...)` existente:

```php
it('does not throw when the model omits company_name', function (): void {
    $dto = CandidateWorkExperienceDTO::make([
        'description' => 'Recrutamento e seleção',
        'start_date' => '2023-03-01',
        'is_currently_working_here' => true,
    ]);

    expect($dto->companyName)->toBe('')
        ->and($dto->description)->toBe('Recrutamento e seleção');
});

it('does not throw when the model omits description', function (): void {
    $dto = CandidateWorkExperienceDTO::make([
        'company_name' => 'Nubank',
        'start_date' => '2023-03-01',
    ]);

    expect($dto->description)->toBe('');
});

it('defaults position to null and skills to an empty list', function (): void {
    $dto = CandidateWorkExperienceDTO::make([
        'company_name' => 'Nubank',
        'description' => 'Recrutamento',
    ]);

    expect($dto->position)->toBeNull()
        ->and($dto->skills)->toBe([]);
});

it('hydrates position and skills when the model provides them', function (): void {
    $dto = CandidateWorkExperienceDTO::make([
        'company_name' => 'Nubank',
        'description' => 'Recrutamento',
        'position' => 'Analista de RH Pleno',
        'skills' => ['Gupy', 'LinkedIn Recruiter'],
    ]);

    expect($dto->position)->toBe('Analista de RH Pleno')
        ->and($dto->skills)->toBe(['Gupy', 'LinkedIn Recruiter']);
});

it('discards empty and non-string entries from skills', function (): void {
    $dto = CandidateWorkExperienceDTO::make([
        'company_name' => 'Nubank',
        'description' => 'Recrutamento',
        'skills' => ['Gupy', '', null, 'Excel'],
    ]);

    expect($dto->skills)->toBe(['Gupy', 'Excel']);
});

it('survives a jsonSerialize round-trip with the new fields', function (): void {
    $original = CandidateWorkExperienceDTO::make([
        'company_name' => 'Nubank',
        'description' => 'Recrutamento',
        'position' => 'Analista de RH Pleno',
        'skills' => ['Gupy'],
        'start_date' => '2023-03-01',
        'is_currently_working_here' => true,
    ]);

    $restored = CandidateWorkExperienceDTO::make($original->jsonSerialize());

    expect($restored->position)->toBe('Analista de RH Pleno')
        ->and($restored->skills)->toBe(['Gupy'])
        ->and($restored->companyName)->toBe('Nubank');
});
```

E no `describe('CandidateEducationDTO', ...)`:

```php
it('does not throw when the model omits institution or degree', function (): void {
    $dto = CandidateEducationDTO::make([
        'field_of_study' => 'Psicologia',
        'start_date' => '2018-01-01',
    ]);

    expect($dto->institution)->toBe('')
        ->and($dto->degree)->toBe('')
        ->and($dto->isEnrolled)->toBeFalse();
});
```

- [ ] **Passo 2: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="does not throw when the model omits company_name"
```

Esperado: FAIL com `Undefined array key "company_name"` — é exatamente o erro de produção.

- [ ] **Passo 3: Tornar o DTO de experiência tolerante e adicionar os campos**

Em `CandidateWorkExperienceDTO`, o construtor passa a ser:

```php
public function __construct(
    public string $companyName,
    public string $description,
    public bool $isCurrentlyWorking,
    public ?string $position = null,
    /** @var list<string> */
    public array $skills = [],
    public CarbonImmutable|Carbon|null $startDate = null,
    public Carbon|CarbonImmutable|null $endDate = null,
) {}
```

E `make()`:

```php
public static function make(array $data): self
{
    return new self(
        companyName: (string) ($data['company_name'] ?? ''),
        description: (string) ($data['description'] ?? ''),
        isCurrentlyWorking: (bool) ($data['is_currently_working_here'] ?? false),
        position: filled($data['position'] ?? null) ? (string) $data['position'] : null,
        skills: self::normalizeSkills($data['skills'] ?? []),
        startDate: (filled($data['start_date'] ?? null) && $data['start_date'] !== 'null')
            ? Date::parse($data['start_date'])
            : null,
        endDate: (filled($data['end_date'] ?? null) && $data['end_date'] !== 'null')
            ? Date::parse($data['end_date'])
            : null,
    );
}

/**
 * @param  mixed  $skills
 * @return list<string>
 */
private static function normalizeSkills(mixed $skills): array
{
    if (! is_array($skills)) {
        return [];
    }

    return array_values(array_filter(
        array_map(fn (mixed $skill): string => is_scalar($skill) ? trim((string) $skill) : '', $skills),
        fn (string $skill): bool => $skill !== '',
    ));
}
```

E `jsonSerialize()`:

```php
/**
 * @return array{company_name: string, position: string|null, description: string, skills: list<string>, start_date: string, end_date: null|string, is_currently_working_here: bool}
 */
public function jsonSerialize(): array
{
    return [
        'company_name' => $this->companyName,
        'position' => $this->position,
        'description' => $this->description,
        'skills' => $this->skills,
        'start_date' => ($this->startDate ?? now())->format('Y-m-d'),
        'end_date' => $this->endDate?->format('Y-m-d'),
        'is_currently_working_here' => $this->isCurrentlyWorking,
    ];
}
```

- [ ] **Passo 4: Tornar o DTO de formação tolerante**

Em `CandidateEducationDTO::make()`, trocar os quatro acessos diretos:

```php
institution: (string) ($data['institution'] ?? ''),
degree: (string) ($data['degree'] ?? ''),
fieldOfStudy: (string) ($data['field_of_study'] ?? ''),
isEnrolled: (bool) ($data['is_enrolled'] ?? false),
```

- [ ] **Passo 5: Proteger o `validate()` da Action**

Em `CompleteOnboardingAction.php:166`:

```php
// antes
if ($output['is_cv'] === true) {

// depois
if (($output['is_cv'] ?? false) === true) {
```

- [ ] **Passo 6: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=CandidateDTO
php artisan test --compact --filter=CompleteOnboardingAction
```

Esperado: tudo verde.

- [ ] **Passo 7: Commit**

```bash
git add app-modules/candidates/src/DTOs app-modules/candidates/src/Actions/Onboarding/CompleteOnboardingAction.php app-modules/candidates/tests/Feature/CandidateDTOTest.php
git commit -m "fix(candidates): tolerate missing fields when hydrating extracted cv data"
```

---

## Task 3: Value object e cast do metadata

**Arquivos:**

- Criar: `app-modules/candidates/src/DTOs/WorkExperienceMetadata.php`
- Criar: `app-modules/candidates/src/Casts/AsWorkExperienceMetadata.php`
- Criar: `app-modules/candidates/tests/Unit/WorkExperienceMetadataTest.php`

**Interfaces:**

- Consome: nada.
- Produz: `WorkExperienceMetadata` com `public array $skills` (lista de strings),
  `::fromArray(array): self`, `->toArray(): array{skills: list<string>}`; e
  `AsWorkExperienceMetadata` implementando `CastsAttributes`, cujo `get()` **sempre**
  devolve uma instância (nunca `null`). Consumido pelas Tasks 4, 5, 7 e 8.

- [ ] **Passo 1: Escrever o teste que falha**

Criar `app-modules/candidates/tests/Unit/WorkExperienceMetadataTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Candidates\Casts\AsWorkExperienceMetadata;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\WorkExperience;

describe('WorkExperienceMetadata', function (): void {
    it('defaults skills to an empty list', function (): void {
        expect((new WorkExperienceMetadata())->skills)->toBe([]);
    });

    it('hydrates skills from an array', function (): void {
        $metadata = WorkExperienceMetadata::fromArray(['skills' => ['Gupy', 'Excel']]);

        expect($metadata->skills)->toBe(['Gupy', 'Excel']);
    });

    it('discards empty entries when hydrating', function (): void {
        $metadata = WorkExperienceMetadata::fromArray(['skills' => ['Gupy', '', null]]);

        expect($metadata->skills)->toBe(['Gupy']);
    });

    it('hydrates to an empty instance from an unknown shape', function (): void {
        expect(WorkExperienceMetadata::fromArray([])->skills)->toBe([])
            ->and(WorkExperienceMetadata::fromArray(['team_size' => 12])->skills)->toBe([]);
    });

    it('serializes to a single skills key', function (): void {
        $metadata = new WorkExperienceMetadata(['Gupy']);

        expect($metadata->toArray())->toBe(['skills' => ['Gupy']])
            ->and($metadata->jsonSerialize())->toBe(['skills' => ['Gupy']]);
    });
});

describe('AsWorkExperienceMetadata', function (): void {
    it('returns an empty instance when the column is null', function (): void {
        $value = (new AsWorkExperienceMetadata())
            ->get(new WorkExperience(), 'metadata', null, []);

        expect($value)->toBeInstanceOf(WorkExperienceMetadata::class)
            ->and($value->skills)->toBe([]);
    });

    it('decodes a json string from the database', function (): void {
        $value = (new AsWorkExperienceMetadata())
            ->get(new WorkExperience(), 'metadata', '{"skills":["Gupy"]}', []);

        expect($value->skills)->toBe(['Gupy']);
    });

    it('accepts an already decoded array', function (): void {
        $value = (new AsWorkExperienceMetadata())
            ->get(new WorkExperience(), 'metadata', ['skills' => ['Excel']], []);

        expect($value->skills)->toBe(['Excel']);
    });

    it('encodes the value object for storage', function (): void {
        $stored = (new AsWorkExperienceMetadata())
            ->set(new WorkExperience(), 'metadata', new WorkExperienceMetadata(['Gupy']), []);

        expect($stored)->toBe('{"skills":["Gupy"]}');
    });

    it('encodes a plain array for storage', function (): void {
        $stored = (new AsWorkExperienceMetadata())
            ->set(new WorkExperience(), 'metadata', ['skills' => ['Gupy']], []);

        expect($stored)->toBe('{"skills":["Gupy"]}');
    });

    it('stores null when the value is null', function (): void {
        $stored = (new AsWorkExperienceMetadata())
            ->set(new WorkExperience(), 'metadata', null, []);

        expect($stored)->toBeNull();
    });
});
```

- [ ] **Passo 2: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter=WorkExperienceMetadata
```

Esperado: FAIL com `Class "He4rt\Candidates\DTOs\WorkExperienceMetadata" not found`.

- [ ] **Passo 3: Criar o value object**

Criar `app-modules/candidates/src/DTOs/WorkExperienceMetadata.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Dados acessórios de uma experiência profissional, extraídos do currículo.
 *
 * Deliberadamente plano: o objeto vive apenas do Eloquent para a apresentação —
 * DTOs e state de formulário trabalham com primitivos, então ele nunca cruza a
 * serialização do Livewire.
 *
 * @implements Arrayable<string, list<string>>
 */
final readonly class WorkExperienceMetadata implements Arrayable, JsonSerializable
{
    /**
     * @param  list<string>  $skills
     */
    public function __construct(
        public array $skills = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $skills = $data['skills'] ?? [];

        if (! is_array($skills)) {
            return new self();
        }

        return new self(
            skills: array_values(array_filter(
                array_map(fn (mixed $skill): string => is_scalar($skill) ? trim((string) $skill) : '', $skills),
                fn (string $skill): bool => $skill !== '',
            )),
        );
    }

    /**
     * @return array{skills: list<string>}
     */
    public function toArray(): array
    {
        return ['skills' => $this->skills];
    }

    /**
     * @return array{skills: list<string>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
```

- [ ] **Passo 4: Criar o cast**

Criar `app-modules/candidates/src/Casts/AsWorkExperienceMetadata.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Candidates\Casts;

use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom casts são invocados mesmo com valor nulo, então `get()` sempre devolve
 * uma instância — o consumidor lê `$experience->metadata->skills` sem `?->`.
 *
 * @implements CastsAttributes<WorkExperienceMetadata, WorkExperienceMetadata|array<string, mixed>>
 */
final class AsWorkExperienceMetadata implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): WorkExperienceMetadata
    {
        $data = is_string($value) ? json_decode($value, true) : $value;

        return WorkExperienceMetadata::fromArray(is_array($data) ? $data : []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $data = $value instanceof WorkExperienceMetadata
            ? $value->toArray()
            : WorkExperienceMetadata::fromArray((array) $value)->toArray();

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
```

- [ ] **Passo 5: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=WorkExperienceMetadata
```

Esperado: 11 passando.

- [ ] **Passo 6: Commit**

```bash
git add app-modules/candidates/src/DTOs/WorkExperienceMetadata.php app-modules/candidates/src/Casts app-modules/candidates/tests/Unit/WorkExperienceMetadataTest.php
git commit -m "feat(candidates): add typed work experience metadata value object and cast"
```

---

## Task 4: Coluna `position`, model e factory

**Arquivos:**

- Criar: `app-modules/candidates/database/migrations/..._add_position_to_candidate_work_experiences_table.php`
- Modificar: `app-modules/candidates/src/Models/WorkExperience.php`
- Modificar: `app-modules/candidates/database/factories/WorkExperienceFactory.php`
- Testar: `app-modules/candidates/tests/Feature/WorkExperienceTest.php`

**Interfaces:**

- Consome: `WorkExperienceMetadata` e `AsWorkExperienceMetadata` da Task 3.
- Produz: `WorkExperience::$position` (`?string`) e `WorkExperience::$metadata`
  (`WorkExperienceMetadata`, nunca nulo). Consumido pelas Tasks 5, 7 e 8.

- [ ] **Passo 1: Escrever o teste que falha**

Acrescentar em `app-modules/candidates/tests/Feature/WorkExperienceTest.php`:

```php
it('persists and reads back the position column', function (): void {
    $experience = WorkExperience::factory()->create(['position' => 'Analista de RH Pleno']);

    expect($experience->fresh()->position)->toBe('Analista de RH Pleno');
});

it('allows a null position for legacy records', function (): void {
    $experience = WorkExperience::factory()->create(['position' => null]);

    expect($experience->fresh()->position)->toBeNull();
});

it('casts metadata to the value object', function (): void {
    $experience = WorkExperience::factory()->create([
        'metadata' => new WorkExperienceMetadata(['Gupy', 'Excel']),
    ]);

    expect($experience->fresh()->metadata)
        ->toBeInstanceOf(WorkExperienceMetadata::class)
        ->and($experience->fresh()->metadata->skills)->toBe(['Gupy', 'Excel']);
});

it('returns an empty metadata object when the column is null', function (): void {
    $experience = WorkExperience::factory()->create(['metadata' => null]);

    expect($experience->fresh()->metadata)
        ->toBeInstanceOf(WorkExperienceMetadata::class)
        ->and($experience->fresh()->metadata->skills)->toBe([]);
});

it('no longer seeds team_size or project_type', function (): void {
    $experience = WorkExperience::factory()->create();

    $raw = json_decode(
        (string) DB::table('candidate_work_experiences')
            ->where('id', $experience->getKey())
            ->value('metadata'),
        true,
    );

    expect($raw)->toHaveKey('skills')
        ->not->toHaveKey('team_size')
        ->not->toHaveKey('project_type')
        ->not->toHaveKey('position');   // agora é coluna
});
```

Adicionar no topo do arquivo:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use Illuminate\Support\Facades\DB;
```

> A asserção lê a coluna bruta via query builder, sem passar pelo cast — é o que prova
> que o factory parou de gravar as chaves fantasma.

- [ ] **Passo 2: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="persists and reads back the position column"
```

Esperado: FAIL — a coluna `position` não existe.

- [ ] **Passo 3: Criar a migration**

```bash
php artisan make:migration add_position_to_candidate_work_experiences_table --module=candidates --no-interaction
```

Conteúdo do `up()`:

```php
public function up(): void
{
    Schema::table('candidate_work_experiences', function (Blueprint $table): void {
        $table->string('position')->nullable()->after('company_name');
    });
}
```

- [ ] **Passo 4: Rodar a migration**

```bash
php artisan migrate
```

- [ ] **Passo 5: Ligar o cast e atualizar o PHPDoc do model**

Em `app-modules/candidates/src/Models/WorkExperience.php`, adicionar os imports:

```php
use He4rt\Candidates\Casts\AsWorkExperienceMetadata;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use Illuminate\Database\Eloquent\Attributes\Table;
```

Substituir o bloco de PHPDoc e os atributos de classe:

```php
/**
 * @property string $id
 * @property string $candidate_id
 * @property string $company_name
 * @property string|null $position
 * @property string $description
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property bool $is_currently_working_here
 * @property WorkExperienceMetadata $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Candidate $candidate
 *
 * @extends BaseModel<WorkExperienceFactory>
 */
#[UsePolicy(WorkExperiencePolicy::class)]
#[UseFactory(WorkExperienceFactory::class)]
#[Table(name: 'candidate_work_experiences')]
class WorkExperience extends BaseModel
```

Remover a linha `protected $table = 'candidate_work_experiences';` (substituída pelo
atributo `#[Table]`), e atualizar `casts()`:

```php
protected function casts(): array
{
    return [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_currently_working_here' => 'boolean',
        'metadata' => AsWorkExperienceMetadata::class,
    ];
}
```

O import de `Illuminate\Support\Collection` fica órfão — remover.

- [ ] **Passo 6: Ajustar o factory**

Em `WorkExperienceFactory::definition()`, `$position` sai do metadata e vira coluna;
`team_size` e `project_type` são removidos. O `return` passa a ser:

```php
return [
    'company_name' => fake()->randomElement($companies),
    'position' => $position,
    'description' => $this->generateJobDescription($position, $techStack),
    'start_date' => $startDate,
    'end_date' => $isCurrentlyWorking ? null : $endDate,
    'is_currently_working_here' => $isCurrentlyWorking,
    'metadata' => new WorkExperienceMetadata($techStack),
    'created_at' => Date::now(),
    'updated_at' => Date::now(),
    'candidate_id' => Candidate::factory(),
];
```

Remover a linha `$teamSize = fake()->numberBetween(3, 15);` e adicionar o import:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
```

- [ ] **Passo 7: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=WorkExperience
```

Esperado: tudo verde.

- [ ] **Passo 8: Commit**

```bash
git add app-modules/candidates/database app-modules/candidates/src/Models/WorkExperience.php app-modules/candidates/tests/Feature/WorkExperienceTest.php
git commit -m "feat(candidates): add position column and typed metadata cast to work experience"
```

---

## Task 5: Persistência de cargo e competências

**Arquivos:**

- Modificar: `app-modules/candidates/src/Actions/Onboarding/StoreCandidateWorkExperiences.php`
- Testar: `app-modules/candidates/tests/Feature/StoreCandidateActionsTest.php`

**Interfaces:**

- Consome: `CandidateWorkExperienceDTO` (Task 2), `WorkExperienceMetadata` (Task 3),
  coluna `position` (Task 4).
- Produz: nada consumido por tarefas posteriores.

- [ ] **Passo 1: Escrever os testes que falham**

Acrescentar em `StoreCandidateActionsTest.php`, dentro do
`describe('StoreCandidateWorkExperiences', ...)`:

```php
it('persists position and skills from the extracted dto', function (): void {
    $dto = new CandidateWorkExperienceDTO(
        companyName: 'Nubank',
        description: 'Recrutamento e seleção',
        isCurrentlyWorking: true,
        position: 'Analista de RH Pleno',
        skills: ['Gupy', 'LinkedIn Recruiter'],
        startDate: Date::parse('2023-03-01'),
    );

    resolve(StoreCandidateWorkExperiences::class)->execute(
        new CandidateWorkExperienceCollection([$dto])
    );

    $record = WorkExperience::query()->firstOrFail();

    expect($record->position)->toBe('Analista de RH Pleno')
        ->and($record->metadata->skills)->toBe(['Gupy', 'LinkedIn Recruiter']);
});

it('persists a null position when the model did not extract one', function (): void {
    $dto = new CandidateWorkExperienceDTO(
        companyName: 'Nubank',
        description: 'Recrutamento',
        isCurrentlyWorking: false,
        startDate: Date::parse('2023-03-01'),
    );

    resolve(StoreCandidateWorkExperiences::class)->execute(
        new CandidateWorkExperienceCollection([$dto])
    );

    $record = WorkExperience::query()->firstOrFail();

    expect($record->position)->toBeNull()
        ->and($record->metadata->skills)->toBe([]);
});

it('persists an experience even when the description is empty', function (): void {
    $dto = new CandidateWorkExperienceDTO(
        companyName: 'Nubank',
        description: '',
        isCurrentlyWorking: false,
        position: 'Analista de RH',
        startDate: Date::parse('2023-03-01'),
    );

    resolve(StoreCandidateWorkExperiences::class)->execute(
        new CandidateWorkExperienceCollection([$dto])
    );

    assertDatabaseCount(WorkExperience::class, 1);
    assertDatabaseHas(WorkExperience::class, ['company_name' => 'Nubank', 'description' => '']);
});

it('skips an experience without a company name', function (): void {
    $dto = new CandidateWorkExperienceDTO(
        companyName: '',
        description: 'Alguma coisa',
        isCurrentlyWorking: false,
        startDate: Date::parse('2023-03-01'),
    );

    resolve(StoreCandidateWorkExperiences::class)->execute(
        new CandidateWorkExperienceCollection([$dto])
    );

    assertDatabaseCount(WorkExperience::class, 0);
});

it('does not overwrite an existing experience on cv re-upload', function (): void {
    $existing = WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'company_name' => 'Nubank',
            'start_date' => Date::parse('2023-03-01')->startOfDay(),
            'position' => 'Cargo digitado pelo candidato',
        ]);

    $dto = new CandidateWorkExperienceDTO(
        companyName: 'Nubank',
        description: 'Recrutamento',
        isCurrentlyWorking: false,
        position: 'Cargo extraído pela IA',
        startDate: Date::parse('2023-03-01'),
    );

    resolve(StoreCandidateWorkExperiences::class)->execute(
        new CandidateWorkExperienceCollection([$dto])
    );

    assertDatabaseCount(WorkExperience::class, 1);
    expect($existing->fresh()->position)->toBe('Cargo digitado pelo candidato');
});
```

- [ ] **Passo 2: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="persists position and skills from the extracted dto"
```

Esperado: FAIL — `position` vem nulo, porque a Action ainda não grava.

- [ ] **Passo 3: Implementar a gravação**

Substituir o corpo de `StoreCandidateWorkExperiences::execute()`:

```php
public function execute(CandidateWorkExperienceCollection $experiences): void
{
    /** @var Candidate $candidate */
    $candidate = auth()->user()->candidate;

    foreach ($experiences->jsonSerialize() as $experience) {
        if (blank($experience->companyName)) {
            logger()->warning('Work experience skipped: missing company name', [
                'candidate_id' => $candidate->id,
            ]);

            continue;
        }

        $attributes = $experience->jsonSerialize();
        unset($attributes['skills']);

        $candidate->workExperiences()->firstOrCreate(
            [
                'company_name' => $experience->companyName,
                'start_date' => ($experience->startDate ?? now())->startOfDay(),
            ],
            [
                ...$attributes,
                'metadata' => new WorkExperienceMetadata($experience->skills),
            ],
        );
    }
}
```

Adicionar o import:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
```

> `skills` sai do array de atributos porque não é coluna — quem guarda a lista é
> `metadata`, via cast. Deixá-la ali dependeria do Eloquent descartá-la silenciosamente.

- [ ] **Passo 4: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=StoreCandidateWorkExperiences
```

Esperado: tudo verde, incluindo os testes de duplicidade que já existiam.

- [ ] **Passo 5: Commit**

```bash
git add app-modules/candidates/src/Actions/Onboarding/StoreCandidateWorkExperiences.php app-modules/candidates/tests/Feature/StoreCandidateActionsTest.php
git commit -m "feat(candidates): persist position and skills from extracted work experiences"
```

---

## Task 6: Cargo e competências no wizard de onboarding

**Arquivos:**

- Modificar: `app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php:330-350`
- Modificar: `app-modules/panel-app/lang/en/pages/onboarding.php`
- Modificar: `app-modules/panel-app/lang/pt_BR/pages/onboarding.php`
- Testar: `app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php`

**Interfaces:**

- Consome: `CandidateWorkExperienceDTO::jsonSerialize()` (Task 2) — chaves `position` e
  `skills` no state do formulário; `StoreCandidateWorkExperiences` (Task 5).
- Produz: nada consumido por tarefas posteriores.

- [ ] **Passo 1: Adicionar as chaves de tradução**

Em `app-modules/panel-app/lang/pt_BR/pages/onboarding.php`, dentro de
`steps.profile.fields`:

```php
'position' => 'Cargo',
'skills' => 'Competências e tecnologias',
```

E em `steps.profile`, criar a seção de placeholders (ou acrescentar, se já existir):

```php
'placeholders' => [
    'position' => 'ex.: Analista de RH Pleno',
    'skills' => 'Digite e pressione Enter',
],
```

Em `app-modules/panel-app/lang/en/pages/onboarding.php`, nas mesmas posições:

```php
'position' => 'Role',
'skills' => 'Skills and technologies',
```

```php
'placeholders' => [
    'position' => 'e.g. Senior HR Analyst',
    'skills' => 'Type and press Enter',
],
```

- [ ] **Passo 2: Escrever o teste que falha**

O `beforeEach` do arquivo já cria `$this->user` e `$this->candidate`, chama `actingAs()`
e ativa o painel — não recrie nada disso. Siga o padrão dos testes vizinhos, que
preenchem o state via `->set('data.*')` e validam com o nome de schema `'content'`.

Acrescentar em `OnboardingWizardTest.php`, dentro do `describe` que já contém
`blocks finalization when a work experience is missing company_name without TypeError`:

```php
it('blocks finalization when a work experience has no position', function (): void {
    livewire(OnboardingWizard::class)
        ->set('wizardVisible', true)
        ->set('data.timezone', 'America/Sao_Paulo')
        ->set('data.preferred_language', 'pt_BR')
        ->set('data.phone', '+5511987654321')
        ->set('data.data_consent_given', true)
        ->set('data.expected_salary', '50000')
        ->set('data.expected_salary_currency', 'BRL')
        ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
        ->set('data.experience_level', ExperienceLevelEnum::Junior->value)
        ->set('data.confirm_submission', true)
        ->set('data.work_experiences', [
            'item-1' => [
                'company_name' => 'Nubank',
                'position' => null,
                'description' => 'Recrutamento e seleção',
                'skills' => [],
                'start_date' => '2023-03-01',
                'end_date' => null,
                'is_currently_working_here' => true,
            ],
        ])
        ->set('data.education', [])
        ->call('handleRegistration')
        ->assertHasFormErrors(['work_experiences.item-1.position'], 'content')
        ->assertNoRedirect();

    expect($this->candidate->fresh()->is_onboarded)->toBeFalse();
});

it('stores position and skills filled in the wizard', function (): void {
    livewire(OnboardingWizard::class)
        ->set('wizardVisible', true)
        ->set('data.timezone', 'America/Sao_Paulo')
        ->set('data.preferred_language', 'pt_BR')
        ->set('data.phone', '+5511987654321')
        ->set('data.data_consent_given', true)
        ->set('data.expected_salary', '50000')
        ->set('data.expected_salary_currency', 'BRL')
        ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
        ->set('data.experience_level', ExperienceLevelEnum::Junior->value)
        ->set('data.confirm_submission', true)
        ->set('data.work_experiences', [
            'item-1' => [
                'company_name' => 'Nubank',
                'position' => 'Analista de RH Pleno',
                'description' => 'Recrutamento e seleção',
                'skills' => ['Gupy'],
                'start_date' => '2023-03-01',
                'end_date' => null,
                'is_currently_working_here' => true,
            ],
        ])
        ->set('data.education', [])
        ->call('handleRegistration')
        ->assertHasNoFormErrors([], 'content');

    $record = WorkExperience::query()->firstOrFail();

    expect($record->position)->toBe('Analista de RH Pleno')
        ->and($record->metadata->skills)->toBe(['Gupy']);
});
```

Adicionar o import que falta no topo do arquivo:

```php
use He4rt\Candidates\Models\WorkExperience;
```

> O teste existente `blocks finalization when a work experience is missing company_name`
> continua verde: `assertHasFormErrors()` verifica presença do erro, não exclusividade —
> ele passará a acusar `position` também, sem quebrar a asserção.

- [ ] **Passo 3: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="blocks finalization when a work experience has no position"
```

Esperado: FAIL — o campo `position` não existe no formulário.

- [ ] **Passo 4: Adicionar os campos ao Repeater**

Em `OnboardingWizard.php`, no `Repeater::make('work_experiences')`, o `schema()` passa a
ser:

```php
->schema([
    TextInput::make('company_name')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.company_name'))
        ->required(),
    TextInput::make('position')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.position'))
        ->placeholder(__('panel-app::pages/onboarding.steps.profile.placeholders.position'))
        ->maxLength(255)
        ->required(),
    Textarea::make('description')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.description'))
        ->rows(3)
        ->required(),
    TagsInput::make('skills')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.skills'))
        ->placeholder(__('panel-app::pages/onboarding.steps.profile.placeholders.skills'))
        ->suggestions(fn (): array => Skill::query()->orderBy('name')->pluck('name')->all())
        ->trim()
        ->columnSpanFull(),
    DatePicker::make('start_date')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.start_date'))
        ->required(),
    DatePicker::make('end_date')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.end_date'))
        ->required(fn (Get $get) => $get('is_currently_working_here') === false),
    Toggle::make('is_currently_working_here')
        ->label(__('panel-app::pages/onboarding.steps.profile.fields.is_currently_working_here')),
])
->itemLabel(fn (array $state): ?string => filled($state['position'] ?? null)
    ? sprintf('%s · %s', $state['position'], $state['company_name'] ?? '')
    : ($state['company_name'] ?? null))
->columnSpanFull(),
```

Adicionar os imports no topo do arquivo:

```php
use Filament\Forms\Components\TagsInput;
use He4rt\Candidates\Models\Skill;
```

- [ ] **Passo 5: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=OnboardingWizard
```

Esperado: tudo verde.

- [ ] **Passo 6: Commit**

```bash
git add app-modules/panel-app/src/Filament/Pages/OnboardingWizard.php app-modules/panel-app/lang app-modules/panel-app/tests/Feature/Filament/Pages/OnboardingWizardTest.php
git commit -m "feat(panel-app): capture position and skills in the onboarding wizard"
```

---

## Task 7: Cargo e competências no perfil do candidato

**Arquivos:**

- Modificar: `app-modules/panel-app/src/Livewire/MyProfile/CandidateWorkExperience.php`
- Modificar: `app-modules/panel-app/lang/{en,pt_BR}/pages/settings.php`
- Testar: `app-modules/panel-app/tests/Feature/Filament/MyProfile/CandidateWorkExperienceTest.php`

**Interfaces:**

- Consome: `WorkExperience::$position` e `$metadata` (Task 4).
- Produz: nada consumido por tarefas posteriores.

- [ ] **Passo 1: Adicionar as chaves de tradução**

Em `app-modules/panel-app/lang/pt_BR/pages/settings.php`, em `work_experience.fields`:

```php
'position' => 'Cargo',
'skills' => 'Competências e tecnologias',
```

Em `work_experience.placeholders`:

```php
'position' => 'ex.: Analista de RH Pleno',
'skills' => 'Digite e pressione Enter',
```

E uma seção nova em `work_experience`:

```php
'helpers' => [
    'position' => 'Ajuda recrutadores a entender sua trajetória profissional.',
],
```

Em `app-modules/panel-app/lang/en/pages/settings.php`, nas mesmas posições:

```php
'position' => 'Role',
'skills' => 'Skills and technologies',
```

```php
'position' => 'e.g. Senior HR Analyst',
'skills' => 'Type and press Enter',
```

```php
'helpers' => [
    'position' => 'Helps recruiters understand your career path.',
],
```

- [ ] **Passo 2: Escrever os testes que falham**

Acrescentar em `CandidateWorkExperienceTest.php`:

```php
it('loads position and skills from existing records', function (): void {
    $user = User::factory()->create();
    $candidate = $user->candidate;

    WorkExperience::factory()->for($candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => 'Analista de RH Pleno',
        'metadata' => new WorkExperienceMetadata(['Gupy']),
    ]);

    livewire(CandidateWorkExperience::class)
        ->actingAs($user)
        ->assertFormSet(fn (array $state): array => [
            'work_experiences.0.position' => 'Analista de RH Pleno',
            'work_experiences.0.skills' => ['Gupy'],
        ]);
});

it('saves without a position, so legacy records do not block the form', function (): void {
    $user = User::factory()->create();
    $candidate = $user->candidate;

    WorkExperience::factory()->for($candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => null,
        'metadata' => null,
    ]);

    livewire(CandidateWorkExperience::class)
        ->actingAs($user)
        ->call('submit')
        ->assertHasNoFormErrors();
});

it('persists position and skills edited by the candidate', function (): void {
    $user = User::factory()->create();
    $candidate = $user->candidate;

    $experience = WorkExperience::factory()->for($candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => null,
        'metadata' => null,
    ]);

    livewire(CandidateWorkExperience::class)
        ->actingAs($user)
        ->fillForm([
            'work_experiences' => [
                [
                    'id' => $experience->id,
                    'company_name' => 'Nubank',
                    'position' => 'Analista de RH Sênior',
                    'description' => $experience->description,
                    'start_date' => $experience->start_date->format('Y-m-d'),
                    'end_date' => null,
                    'is_currently_working_here' => true,
                    'skills' => ['Gupy', 'Excel'],
                ],
            ],
        ])
        ->call('submit')
        ->assertHasNoFormErrors();

    expect($experience->fresh()->position)->toBe('Analista de RH Sênior')
        ->and($experience->fresh()->metadata->skills)->toBe(['Gupy', 'Excel']);
});
```

Adicionar os imports necessários no topo:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\WorkExperience;
```

- [ ] **Passo 3: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="loads position and skills from existing records"
```

Esperado: FAIL — o formulário não tem os campos.

- [ ] **Passo 4: Carregar os campos no `mount()`**

```php
$this->form->fill([
    'work_experiences' => $candidate->workExperiences->map(fn (WorkExperience $experience) => [
        'id' => $experience->id,
        'company_name' => $experience->company_name,
        'position' => $experience->position,
        'description' => $experience->description,
        'skills' => $experience->metadata->skills,
        'start_date' => $experience->start_date,
        'end_date' => $experience->end_date,
        'is_currently_working_here' => $experience->is_currently_working_here,
    ])->toArray(),
]);
```

- [ ] **Passo 5: Adicionar os campos ao formulário**

No `Repeater::make('work_experiences')`, logo após `company_name`:

```php
TextInput::make('position')
    ->label(__('panel-app::pages/settings.work_experience.fields.position'))
    ->prefixIcon('heroicon-o-identification')
    ->placeholder(__('panel-app::pages/settings.work_experience.placeholders.position'))
    ->helperText(__('panel-app::pages/settings.work_experience.helpers.position'))
    ->maxLength(255)
    ->columnSpanFull(),
```

E após `description`:

```php
TagsInput::make('skills')
    ->label(__('panel-app::pages/settings.work_experience.fields.skills'))
    ->placeholder(__('panel-app::pages/settings.work_experience.placeholders.skills'))
    ->suggestions(fn (): array => Skill::query()->orderBy('name')->pluck('name')->all())
    ->trim()
    ->columnSpanFull(),
```

Trocar o `itemLabel`:

```php
->itemLabel(fn (array $state): ?string => filled($state['position'] ?? null)
    ? sprintf('%s · %s', $state['position'], $state['company_name'] ?? '')
    : ($state['company_name'] ?? null))
```

Imports novos:

```php
use Filament\Forms\Components\TagsInput;
use He4rt\Candidates\Models\Skill;
```

- [ ] **Passo 6: Gravar os campos no `submit()`**

Nos dois ramos (`update` e `create`), o array de atributos passa a incluir:

```php
'position' => $entry['position'] ?? null,
'metadata' => new WorkExperienceMetadata($entry['skills'] ?? []),
```

Import novo:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
```

- [ ] **Passo 7: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=CandidateWorkExperience
```

Esperado: tudo verde.

- [ ] **Passo 8: Commit**

```bash
git add app-modules/panel-app/src/Livewire/MyProfile/CandidateWorkExperience.php app-modules/panel-app/lang app-modules/panel-app/tests
git commit -m "feat(panel-app): edit position and skills in the candidate profile"
```

---

## Task 8: Card do RH lendo dados reais

**Arquivos:**

- Modificar: `app-modules/panel-organization/resources/views/components/applications/tabs/work-experience.blade.php`
- Modificar: `app-modules/panel-organization/lang/{en,pt_BR}/view.php:117`
- Testar: `app-modules/panel-organization/tests/Feature/Filament/Application/WorkExperienceTabTest.php`

**Interfaces:**

- Consome: `WorkExperience::$position` e `$metadata->skills` (Task 4).
- Produz: nada.

- [ ] **Passo 1: Ajustar o texto do fallback**

A chave `professional_role_fallback` já existe nos dois idiomas. Trocar apenas o valor:

`app-modules/panel-organization/lang/pt_BR/view.php:117`:

```php
'professional_role_fallback' => 'Cargo não informado',
```

`app-modules/panel-organization/lang/en/view.php:117`:

```php
'professional_role_fallback' => 'Role not informed',
```

- [ ] **Passo 2: Escrever os testes que falham**

O `beforeEach` do arquivo já cria `$this->candidate`, `$this->application`, o recrutador
com papel de super admin, e ativa painel e tenant. A aba vive dentro de
`Tabs::make('application_tabs')` sem `->lazy()`, então o conteúdo vai ao DOM e
`assertSee()` funciona sobre o componente da página.

Acrescentar em `WorkExperienceTabTest.php`:

```php
it('shows the stored position as the card heading', function (): void {
    $experience = WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH Pleno',
            'description' => 'Recrutamento e seleção de vagas de tecnologia.',
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee('Analista de RH Pleno')
        ->assertSee($experience->company_name);
});

it('falls back to a neutral label when the position is null', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create(['position' => null]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee(__('panel-organization::view.tabs.work_experience.professional_role_fallback'));
});

it('never derives a heading from the description', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => null,
            'description' => 'Analista de RH',
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee(__('panel-organization::view.tabs.work_experience.professional_role_fallback'));
});

it('renders the description in full, without dropping the first line', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH',
            'description' => "Coordenacao de processos\nConducao de entrevistas",
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee('Coordenacao de processos')
        ->assertSee('Conducao de entrevistas');
});

it('renders skills from metadata instead of guessing them from the description', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH',
            'description' => 'Acompanhei times que usavam Laravel no dia a dia.',
            'metadata' => new WorkExperienceMetadata(['Gupy']),
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee('Gupy')
        ->assertDontSee('>Laravel<', escape: false);
});
```

Imports novos:

```php
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
```

> No último teste, a asserção usa `'>Laravel<'` sem escape para mirar a **tag** renderizada,
> não a palavra dentro da descrição — que continua visível e deve continuar. Hoje o
> `extractSkills()` produziria essa tag por causa do `stripos()` na lista fixa.

- [ ] **Passo 3: Rodar e confirmar a falha**

```bash
php artisan test --compact --filter="never derives a heading from the description"
```

Esperado: FAIL — hoje a heurística usa a descrição como cargo.

- [ ] **Passo 4: Remover as três heurísticas do blade**

Apagar todo o bloco `@php` das linhas 20 a 78 (as funções `extractJobTitle`,
`extractSkills` e `formatJobDescription`), mantendo apenas o cabeçalho:

```php
@php
    /** @var \He4rt\Applications\Models\Application $record */
    /** @var \He4rt\Candidates\Models\Candidate $candidate */
    $candidate = $record->candidate;
    $workExperiences = $candidate
        ->workExperiences()
        ->orderBy('start_date', 'desc')
        ->get();
    $hasExperience = $workExperiences->isNotEmpty();

    $currentJob = $workExperiences->where('is_currently_working_here', true)->first();

    $totalExperienceTimeString = $candidate->totalExperienceFormatted;
@endphp
```

- [ ] **Passo 5: Trocar as três chamadas dentro do `@foreach`**

```php
@php
    $isCurrent = $experience->is_currently_working_here;
    $jobTitle = $experience->position
        ?? __('panel-organization::view.tabs.work_experience.professional_role_fallback');
    $skills = $experience->metadata->skills;

    $startDate = $experience->start_date;
    $endDate = $isCurrent ? now() : $experience->end_date;

    $durationText = $candidate->getExperienceDuration($experience);
@endphp
```

E, na renderização da descrição, trocar `$formattedDescription` por
`$experience->description`:

```blade
@if (! empty(trim($experience->description)))
    <div class="text-text-medium mt-4 text-base leading-7">
        {{ $experience->description }}
    </div>
@endif
```

- [ ] **Passo 6: Rodar os testes e confirmar que passam**

```bash
php artisan test --compact --filter=WorkExperienceTab
```

Esperado: tudo verde.

- [ ] **Passo 7: Commit**

```bash
git add app-modules/panel-organization
git commit -m "refactor(panel-organization): read position and skills instead of guessing them"
```

---

## Verificação final

- [ ] **Passo 1: Bateria completa, com paralelismo limitado**

```bash
./vendor/bin/rector process --dry-run --ansi
./vendor/bin/pint --test --ansi
./vendor/bin/phpstan analyse --ansi
nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact
```

- [ ] **Passo 2: Corrigir o que falhar**

Se o Pint acusar formatação: `./vendor/bin/pint --dirty --format agent`.
Se o PHPStan acusar erro no cast ou no value object, prefira corrigir o tipo a
suprimir; se a supressão for inevitável, use bloco indentado com `path` e `count` no
`app-modules/candidates/phpstan.neon`.

- [ ] **Passo 3: Verificar manualmente o fluxo de onboarding**

Suba um CV real pelo wizard e confirme que cargo e competências chegam preenchidos ao
formulário, e que o card do painel da organização exibe o cargo extraído.

- [ ] **Passo 4: Push**

```bash
git push --no-verify
```

---

## Rastreabilidade com a spec

| Requisito da spec                                 | Tarefa |
| ------------------------------------------------- | ------ |
| Contrato explícito com `requiredFields`           | 1      |
| `position` e `skills` extraídos do CV             | 1      |
| Campos opcionais fora do `requiredFields`         | 1      |
| Hidratação tolerante (o crash)                    | 2      |
| `validate()` protegido                            | 2      |
| Value object tipado                               | 3      |
| Cast devolve objeto vazio, nunca nulo             | 3      |
| `position` como coluna                            | 4      |
| `team_size` / `project_type` removidos do factory | 4      |
| PHPDoc e `#[Table]` do model                      | 4      |
| Descrição vazia é aceita                          | 5      |
| Item sem `company_name` é descartado              | 5      |
| Re-upload não atualiza o existente                | 5      |
| Cargo obrigatório só no onboarding                | 6, 7   |
| `itemLabel` com cargo + empresa                   | 6, 7   |
| Heurísticas removidas do card do RH               | 8      |
| Descrição íntegra no card                         | 8      |
