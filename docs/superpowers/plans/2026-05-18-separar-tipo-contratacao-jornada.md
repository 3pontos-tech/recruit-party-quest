# Separação Tipo de Contratação × Jornada de Trabalho — Plano de Implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Separar o eixo "regime jurídico" (`employment_type`) do eixo "jornada/carga horária" (novo `work_schedule`), ambos opcionais, sem inventar dado em produção.

**Architecture:** Novo `WorkScheduleEnum`; `EmploymentTypeEnum` expurgado de valores de jornada; migration única transacional que torna `employment_type` nullable, adiciona `work_schedule` nullable e faz backfill mapeado dos 6 valores legados; consumidores (DTOs, action, IA, 3 painéis Filament, Blade, busca) atualizados para tolerar `NULL` = "Não informado".

**Tech Stack:** Laravel 12, PHP 8.4, Filament v5, Pest v4, PostgreSQL, módulo `app-modules/recruitment`.

**Spec:** `docs/superpowers/specs/2026-05-18-separar-tipo-contratacao-jornada-design.md`

---

## Estrutura de arquivos

| Arquivo | Responsabilidade | Ação |
|---|---|---|
| `app-modules/recruitment/src/Requisitions/Enums/WorkScheduleEnum.php` | Enum jornada | Criar |
| `app-modules/recruitment/src/Requisitions/Enums/EmploymentTypeEnum.php` | Enum regime corrigido | Modificar |
| `app-modules/recruitment/lang/{en,pt_BR}/requisitions.php` | Labels enums | Modificar |
| `app-modules/recruitment/lang/{en,pt_BR}/filament.php` | Labels de campo | Modificar |
| `app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php` | Schema + backfill | Criar |
| `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php` | Cast + docblock | Modificar |
| `app-modules/recruitment/src/Requisitions/DTOs/JobRequisitionDTO.php` | DTO | Modificar |
| `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisitionDTO.php` | DTO IA | Modificar |
| `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisition.php` | Prompt + passthrough | Modificar |
| `app-modules/recruitment/src/Requisitions/Actions/StoreJobRequisitionAction.php` | Persistência | Modificar |
| `app-modules/recruitment/database/factories/JobRequisitionFactory.php` | Factory | Modificar |
| `app-modules/panel-admin/.../JobRequisitions/Schemas/JobRequisitionForm.php` | Form admin | Modificar |
| `app-modules/panel-organization/.../JobRequisitions/Schemas/JobRequisitionForm.php` | Form org | Modificar |
| `app-modules/panel-organization/.../JobRequisitions/Actions/GenerateJobRequisitionAction.php` | Form IA | Modificar |
| `app-modules/panel-app/.../JobRequisitions/Schemas/JobRequisitionForm.php` | Form app | Modificar |
| `app-modules/panel-{admin,organization,app}/.../JobRequisitions/Tables/JobRequisitionsTable.php` | Coluna tabela | Modificar |
| `app-modules/panel-app/resources/views/components/jobs/job-{card,description}.blade.php` | Exibição | Modificar |
| `app-modules/panel-app/src/Livewire/{SearchJobs,JobRecommendations}.php` | Filtro busca | Modificar |
| `app-modules/panel-app/lang/{en,pt_BR}/pages/onboarding.php` | Placeholder | Modificar |

---

## Task 1: Criar `WorkScheduleEnum`

**Context:** Não existe enum de jornada. Ele será espelho fiel de `WorkArrangementEnum` (mesmo módulo, mesmo `StringifyEnum`, mesmas interfaces). É a fundação de tudo.

**Files:**
- Create: `app-modules/recruitment/src/Requisitions/Enums/WorkScheduleEnum.php`
- Test: `app-modules/recruitment/tests/Unit/WorkScheduleEnumTest.php`

**Comportamento esperado (BDD):**
- Given o enum `WorkScheduleEnum`, Then expõe exatamente os casos `full_time`, `part_time`, `hourly`, `shift`.
- Given um caso, When chamo `getLabel()`, Then retorna a tradução de `recruitment::requisitions.work_schedule.<value>.label`.
- Given um caso, When chamo `getColor()`/`getIcon()`, Then retorna valor não vazio.

- [ ] **Step 1: Escrever o teste que falha**

Create `app-modules/recruitment/tests/Unit/WorkScheduleEnumTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;

it('has exactly the four schedule cases', function (): void {
    expect(array_map(fn (WorkScheduleEnum $c): string => $c->value, WorkScheduleEnum::cases()))
        ->toBe(['full_time', 'part_time', 'hourly', 'shift']);
});

it('resolves label, color and icon for every case', function (WorkScheduleEnum $case): void {
    expect($case->getLabel())->toBeString()->not->toBeEmpty()
        ->and($case->getColor())->not->toBeEmpty()
        ->and($case->getIcon())->not->toBeNull();
})->with(WorkScheduleEnum::cases());
```

- [ ] **Step 2: Rodar o teste e confirmar a falha**

Run: `php artisan test --compact --filter=WorkScheduleEnumTest`
Expected: FAIL — "Class WorkScheduleEnum not found".

- [ ] **Step 3: Implementar o enum**

Create `app-modules/recruitment/src/Requisitions/Enums/WorkScheduleEnum.php`:

```php
<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum WorkScheduleEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Hourly = 'hourly';
    case Shift = 'shift';

    public function getColor(): string
    {
        return match ($this) {
            self::FullTime => 'success',
            self::PartTime => 'info',
            self::Hourly => 'warning',
            self::Shift => 'primary',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::FullTime => Heroicon::Clock,
            self::PartTime => Heroicon::ClockSolid,
            self::Hourly => Heroicon::CurrencyDollar,
            self::Shift => Heroicon::ArrowPath,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.work_schedule.'.$this->value.'.label');
    }
}
```

- [ ] **Step 4: Adicionar as traduções**

In `app-modules/recruitment/lang/pt_BR/requisitions.php`, add a chave `work_schedule` logo após o bloco `work_arrangement` (mesmo nível do array raiz):

```php
    'work_schedule' => [
        'full_time' => ['label' => 'Tempo Integral'],
        'part_time' => ['label' => 'Meio Período'],
        'hourly' => ['label' => 'Por Hora'],
        'shift' => ['label' => 'Escala (12x36, 6x1)'],
    ],
```

In `app-modules/recruitment/lang/en/requisitions.php`, add no mesmo ponto:

```php
    'work_schedule' => [
        'full_time' => ['label' => 'Full-time'],
        'part_time' => ['label' => 'Part-time'],
        'hourly' => ['label' => 'Hourly'],
        'shift' => ['label' => 'Shift (12x36, 6x1)'],
    ],
```

- [ ] **Step 5: Rodar o teste e confirmar que passa**

Run: `php artisan test --compact --filter=WorkScheduleEnumTest`
Expected: PASS (5 assertions).

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment/src/Requisitions/Enums/WorkScheduleEnum.php \
        app-modules/recruitment/tests/Unit/WorkScheduleEnumTest.php \
        app-modules/recruitment/lang/en/requisitions.php \
        app-modules/recruitment/lang/pt_BR/requisitions.php
git commit -m "feat(recruitment): add WorkScheduleEnum with i18n"
```

---

## Task 2: Corrigir `EmploymentTypeEnum` (expurgar jornada)

**Context:** O enum hoje tem `FullTimeEmployee`, `Clt`, `Contractor`, `Intern`, `Temporary`, `PartTime`. `FullTimeEmployee`/`PartTime` são jornada; `Intern` (estágio) sai por decisão de produto. Conjunto final: `Clt`, `Contractor`, `Temporary`, `Freelancer` (novo). O valor `contractor` é **preservado** (sem rename no banco); só o label muda.

**Files:**
- Modify: `app-modules/recruitment/src/Requisitions/Enums/EmploymentTypeEnum.php`
- Modify: `app-modules/recruitment/lang/{en,pt_BR}/requisitions.php`
- Test: `app-modules/recruitment/tests/Unit/EmploymentTypeEnumTest.php`

**Comportamento esperado (BDD):**
- Given o enum, Then expõe exatamente `clt`, `contractor`, `temporary`, `freelancer`.
- Given `EmploymentTypeEnum::Contractor`, When `getLabel()`, Then retorna "Contrato/PJ" (pt_BR).
- Given valores antigos `full_time_employee`/`intern`/`part_time`, When `tryFrom()`, Then retorna `null` (não existem mais).

- [ ] **Step 1: Escrever o teste que falha**

Create `app-modules/recruitment/tests/Unit/EmploymentTypeEnumTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;

it('exposes only the four regime cases', function (): void {
    expect(array_map(fn (EmploymentTypeEnum $c): string => $c->value, EmploymentTypeEnum::cases()))
        ->toBe(['clt', 'contractor', 'temporary', 'freelancer']);
});

it('no longer recognizes legacy schedule/intern values', function (string $legacy): void {
    expect(EmploymentTypeEnum::tryFrom($legacy))->toBeNull();
})->with(['full_time_employee', 'part_time', 'intern']);

it('resolves label, color and icon for every case', function (EmploymentTypeEnum $case): void {
    expect($case->getLabel())->toBeString()->not->toBeEmpty()
        ->and($case->getColor())->not->toBeEmpty()
        ->and($case->getIcon())->not->toBeNull();
})->with(EmploymentTypeEnum::cases());
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=EmploymentTypeEnumTest`
Expected: FAIL — casos atuais incluem `full_time_employee`.

- [ ] **Step 3: Reescrever o enum**

Replace `app-modules/recruitment/src/Requisitions/Enums/EmploymentTypeEnum.php` com:

```php
<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmploymentTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case Clt = 'clt';
    case Contractor = 'contractor';
    case Temporary = 'temporary';
    case Freelancer = 'freelancer';

    public function getColor(): array
    {
        return match ($this) {
            self::Clt => Color::Teal,
            self::Contractor => Color::Blue,
            self::Temporary => Color::Amber,
            self::Freelancer => Color::Violet,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Clt => Heroicon::ShieldCheck,
            self::Contractor => Heroicon::DocumentText,
            self::Temporary => Heroicon::Calendar,
            self::Freelancer => Heroicon::Sparkles,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.employment_type.'.$this->value.'.label');
    }
}
```

- [ ] **Step 4: Atualizar traduções**

In `app-modules/recruitment/lang/pt_BR/requisitions.php`, substituir o bloco `'employment_type' => [ ... ]` inteiro por:

```php
    'employment_type' => [
        'clt' => ['label' => 'CLT'],
        'contractor' => ['label' => 'Contrato/PJ'],
        'temporary' => ['label' => 'Temporário'],
        'freelancer' => ['label' => 'Freelancer/Autônomo'],
    ],
```

In `app-modules/recruitment/lang/en/requisitions.php`, substituir o bloco `'employment_type' => [ ... ]` por:

```php
    'employment_type' => [
        'clt' => ['label' => 'CLT (Brazilian Labor Contract)'],
        'contractor' => ['label' => 'Contractor (PJ)'],
        'temporary' => ['label' => 'Temporary'],
        'freelancer' => ['label' => 'Freelancer'],
    ],
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=EmploymentTypeEnumTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment/src/Requisitions/Enums/EmploymentTypeEnum.php \
        app-modules/recruitment/tests/Unit/EmploymentTypeEnumTest.php \
        app-modules/recruitment/lang/en/requisitions.php \
        app-modules/recruitment/lang/pt_BR/requisitions.php
git commit -m "feat(recruitment): purge schedule values from EmploymentTypeEnum"
```

---

## Task 3: Migration — `work_schedule` + nullable + backfill

**Context:** A tabela `recruitment_job_requisitions` já existe em produção (`employment_type` string NOT NULL). Nova migration única, transacional, reversível: torna `employment_type` nullable, adiciona `work_schedule` nullable e faz backfill dos 6 valores legados. Laravel 12 altera coluna nativamente (sem dbal); repetir atributos preexistentes ao alterar (string + comment).

**Files:**
- Create: `app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php`
- Test: `app-modules/recruitment/tests/Feature/SeparateWorkScheduleMigrationTest.php`

**Comportamento esperado (BDD):**
- Given uma linha legada `employment_type='full_time_employee'`, When a migration roda, Then `(employment_type=NULL, work_schedule='full_time')`.
- Given `employment_type='part_time'`, Then `(NULL, 'part_time')`.
- Given `employment_type='intern'`, Then `(NULL, NULL)`.
- Given `employment_type IN ('clt','contractor','temporary')`, Then `employment_type` preservado e `work_schedule=NULL`.
- Given a migration revertida, Then `work_schedule` deixa de existir e jornada volta ao valor único (`full_time`→`full_time_employee`, `part_time`→`part_time`).

**Mudança (antes/depois):** antes só `employment_type` (NOT NULL); depois `employment_type` (nullable) + `work_schedule` (nullable).

- [ ] **Step 1: Escrever o teste que falha**

Create `app-modules/recruitment/tests/Feature/SeparateWorkScheduleMigrationTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('backfills legacy employment_type values into the two axes', function (string $legacy, ?string $expectedType, ?string $expectedSchedule): void {
    // Reverte a migration alvo para simular estado legado.
    $this->artisan('migrate:rollback', ['--step' => 1, '--path' => 'app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php', '--realpath' => false]);

    $id = (string) Str::uuid();
    DB::table('recruitment_job_requisitions')->insert(
        array_merge(jobRequisitionLegacyRow($id), ['employment_type' => $legacy])
    );

    $this->artisan('migrate', ['--path' => 'app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php', '--realpath' => false]);

    $row = DB::table('recruitment_job_requisitions')->where('id', $id)->first();

    expect(Schema::hasColumn('recruitment_job_requisitions', 'work_schedule'))->toBeTrue()
        ->and($row->employment_type)->toBe($expectedType)
        ->and($row->work_schedule)->toBe($expectedSchedule);
})->with([
    'full_time_employee' => ['full_time_employee', null, 'full_time'],
    'part_time' => ['part_time', null, 'part_time'],
    'intern' => ['intern', null, null],
    'clt' => ['clt', 'clt', null],
    'contractor' => ['contractor', 'contractor', null],
    'temporary' => ['temporary', 'temporary', null],
]);
```

Adicionar o helper no topo do arquivo (após os `use`):

```php
function jobRequisitionLegacyRow(string $id): array
{
    $team = He4rt\Teams\Team::factory()->create();
    $dept = He4rt\Teams\Department::factory()->create(['team_id' => $team->id]);
    $user = He4rt\Users\User::factory()->create();

    return [
        'id' => $id,
        'slug' => 'legacy-'.Str::lower(Str::random(6)),
        'team_id' => $team->id,
        'department_id' => $dept->id,
        'work_arrangement' => 'remote',
        'experience_level' => 'junior',
        'positions_available' => '1',
        'salary_currency' => 'BRL',
        'show_salary_to_candidates' => false,
        'created_by_id' => $user->id,
        'status' => 'draft',
        'priority' => 'medium',
        'is_internal_only' => false,
        'is_confidential' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=SeparateWorkScheduleMigrationTest`
Expected: FAIL — migration não existe / coluna `work_schedule` ausente.

- [ ] **Step 3: Criar a migration**

Create `app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                $table->string('employment_type')->nullable()->comment(EmploymentTypeEnum::stringifyCases())->change();
                $table->string('work_schedule')->nullable()->after('employment_type')->comment(WorkScheduleEnum::stringifyCases());
            });

            DB::table('recruitment_job_requisitions')->where('employment_type', 'full_time_employee')
                ->update(['employment_type' => null, 'work_schedule' => 'full_time']);

            DB::table('recruitment_job_requisitions')->where('employment_type', 'part_time')
                ->update(['employment_type' => null, 'work_schedule' => 'part_time']);

            DB::table('recruitment_job_requisitions')->where('employment_type', 'intern')
                ->update(['employment_type' => null]);
            // clt / contractor / temporary: preservados, work_schedule permanece NULL.
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('recruitment_job_requisitions')->where('work_schedule', 'full_time')->whereNull('employment_type')
                ->update(['employment_type' => 'full_time_employee']);

            DB::table('recruitment_job_requisitions')->where('work_schedule', 'part_time')->whereNull('employment_type')
                ->update(['employment_type' => 'part_time']);

            Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                $table->dropColumn('work_schedule');
            });

            DB::table('recruitment_job_requisitions')->whereNull('employment_type')
                ->update(['employment_type' => 'full_time_employee']);

            Schema::table('recruitment_job_requisitions', function (Blueprint $table): void {
                $table->string('employment_type')->nullable(false)->comment(EmploymentTypeEnum::stringifyCases())->change();
            });
        });
    }
};
```

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=SeparateWorkScheduleMigrationTest`
Expected: PASS (6 datasets).

- [ ] **Step 5: Commit**

```bash
git add app-modules/recruitment/database/migrations/2026_05_18_000000_separate_work_schedule_from_employment_type.php \
        app-modules/recruitment/tests/Feature/SeparateWorkScheduleMigrationTest.php
git commit -m "feat(recruitment): migration splitting employment_type and work_schedule with backfill"
```

---

## Task 4: Model `JobRequisition` — cast + docblock

**Context:** `JobRequisition` faz cast de `employment_type` e precisa castar `work_schedule`. Ambos passam a ser anuláveis. Arquivo: casts em `:189-206`, docblock em `:45`.

**Files:**
- Modify: `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php`
- Test: `app-modules/recruitment/tests/Feature/JobRequisitionTest.php` (adicionar caso)

**Comportamento esperado (BDD):**
- Given um `JobRequisition` com `work_schedule='full_time'`, When acesso `$model->work_schedule`, Then é `WorkScheduleEnum::FullTime`.
- Given `employment_type=NULL`, Then `$model->employment_type` é `null` (sem exceção).

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/recruitment/tests/Feature/JobRequisitionTest.php`:

```php
it('casts work_schedule and allows null employment_type', function (): void {
    $model = He4rt\Recruitment\Requisitions\Models\JobRequisition::factory()->create([
        'employment_type' => null,
        'work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::FullTime,
    ]);

    $model->refresh();

    expect($model->employment_type)->toBeNull()
        ->and($model->work_schedule)->toBe(He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::FullTime);
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="casts work_schedule"`
Expected: FAIL — `work_schedule` não castado / coluna não no factory.

- [ ] **Step 3: Adicionar o cast e ajustar docblock**

In `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php`, no método `casts()`, após a linha `'employment_type' => EmploymentTypeEnum::class,` adicionar:

```php
            'work_schedule' => WorkScheduleEnum::class,
```

Adicionar o import no topo (junto aos outros enums):

```php
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
```

No docblock, trocar a linha `* @property EmploymentTypeEnum $employment_type` por:

```php
 * @property EmploymentTypeEnum|null $employment_type
 * @property WorkScheduleEnum|null $work_schedule
```

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="casts work_schedule"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app-modules/recruitment/src/Requisitions/Models/JobRequisition.php \
        app-modules/recruitment/tests/Feature/JobRequisitionTest.php
git commit -m "feat(recruitment): cast work_schedule and allow nullable employment_type on JobRequisition"
```

---

## Task 5: DTOs — `JobRequisitionDTO` e `GenerateJobRequisitionDTO`

**Context:** `JobRequisitionDTO` recebe `EmploymentTypeEnum $employmentType` (não-nulo). Precisa virar `?EmploymentTypeEnum` e ganhar `?WorkScheduleEnum $workSchedule`. `GenerateJobRequisitionDTO::make()` usa `EmploymentTypeEnum::from()` (linha 42) — vira `tryFrom()` + novo campo jornada.

**Files:**
- Modify: `app-modules/recruitment/src/Requisitions/DTOs/JobRequisitionDTO.php`
- Modify: `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisitionDTO.php`
- Test: `app-modules/recruitment/tests/Unit/JobRequisitionDTOTest.php`

**Comportamento esperado (BDD):**
- Given `make()` sem `employment_type` e sem `work_schedule`, Then `employmentType` e `workSchedule` são `null`.
- Given `make()` com `work_schedule=WorkScheduleEnum::Shift`, Then `workSchedule` é `Shift`.
- Given `GenerateJobRequisitionDTO::make()` com `employment_type` ausente, Then `employmentType` é `null` (sem `ValueError`).

- [ ] **Step 1: Escrever o teste que falha**

Create `app-modules/recruitment/tests/Unit/JobRequisitionDTOTest.php`:

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;

it('builds with nullable employment type and optional work schedule', function (): void {
    $dto = JobRequisitionDTO::make([
        'title' => 'Dev', 'department_id' => 'd', 'team_id' => 't', 'recruiter_id' => 'r',
        'description' => 'x', 'experience_level' => ExperienceLevelEnum::Junior,
        'employment_type' => null, 'work_arrangement' => WorkArrangementEnum::Remote,
        'status' => RequisitionStatusEnum::Draft, 'summary' => 's', 'created_by' => 'u', 'items' => [],
    ]);

    expect($dto->employmentType)->toBeNull()->and($dto->workSchedule)->toBeNull();

    $dto2 = JobRequisitionDTO::make([
        'title' => 'Dev', 'department_id' => 'd', 'team_id' => 't', 'recruiter_id' => 'r',
        'description' => 'x', 'experience_level' => ExperienceLevelEnum::Junior,
        'employment_type' => null, 'work_arrangement' => WorkArrangementEnum::Remote,
        'work_schedule' => WorkScheduleEnum::Shift,
        'status' => RequisitionStatusEnum::Draft, 'summary' => 's', 'created_by' => 'u', 'items' => [],
    ]);

    expect($dto2->workSchedule)->toBe(WorkScheduleEnum::Shift);
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=JobRequisitionDTOTest`
Expected: FAIL — propriedade `workSchedule` inexistente.

- [ ] **Step 3: Atualizar `JobRequisitionDTO`**

In `app-modules/recruitment/src/Requisitions/DTOs/JobRequisitionDTO.php`:

Adicionar import:

```php
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
```

No construtor, trocar `public EmploymentTypeEnum $employmentType,` por:

```php
        public ?EmploymentTypeEnum $employmentType,
        public ?WorkScheduleEnum $workSchedule,
```

No `make()`, trocar `employmentType: $data['employment_type'],` por:

```php
            employmentType: $data['employment_type'] ?? null,
            workSchedule: $data['work_schedule'] ?? null,
```

- [ ] **Step 4: Atualizar `GenerateJobRequisitionDTO`**

In `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisitionDTO.php`:

Adicionar import `use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;`.

No construtor, trocar `public EmploymentTypeEnum $employmentType,` por:

```php
        public ?EmploymentTypeEnum $employmentType,
        public ?WorkScheduleEnum $workSchedule,
```

No `make()`, trocar `employmentType: EmploymentTypeEnum::from($data['employment_type']),` por:

```php
            employmentType: isset($data['employment_type']) ? EmploymentTypeEnum::tryFrom($data['employment_type']) : null,
            workSchedule: isset($data['work_schedule']) ? WorkScheduleEnum::tryFrom($data['work_schedule']) : null,
```

No `jsonSerialize()`, após `'employment_type' => $this->employmentType,` adicionar:

```php
            'work_schedule' => $this->workSchedule,
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=JobRequisitionDTOTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment/src/Requisitions/DTOs/JobRequisitionDTO.php \
        app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisitionDTO.php \
        app-modules/recruitment/tests/Unit/JobRequisitionDTOTest.php
git commit -m "feat(recruitment): make employmentType nullable and add workSchedule to DTOs"
```

---

## Task 6: `StoreJobRequisitionAction` + `GenerateJobRequisition` (prompt/passthrough)

**Context:** `StoreJobRequisitionAction` cria o `JobRequisition` mas não grava `work_schedule`. `GenerateJobRequisition` repassa `employment_type` do DTO de input para `JobRequisitionDTO::make()` e injeta no prompt — precisa repassar também `work_schedule` e citar jornada no prompt.

**Files:**
- Modify: `app-modules/recruitment/src/Requisitions/Actions/StoreJobRequisitionAction.php`
- Modify: `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisition.php`
- Test: `app-modules/recruitment/tests/Feature/JobRequisitionTest.php` (caso novo)

**Comportamento esperado (BDD):**
- Given um `JobRequisitionDTO` com `workSchedule=PartTime` e `employmentType=null`, When `StoreJobRequisitionAction::execute()`, Then a linha persistida tem `work_schedule='part_time'` e `employment_type=NULL`.

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/recruitment/tests/Feature/JobRequisitionTest.php`:

```php
it('persists work_schedule and null employment_type via StoreJobRequisitionAction', function (): void {
    $team = He4rt\Teams\Team::factory()->create();
    $dept = He4rt\Teams\Department::factory()->create(['team_id' => $team->id]);
    $recruiter = He4rt\Recruitment\Staff\Recruiter\Recruiter::factory()->create();
    $user = He4rt\Users\User::factory()->create();

    $dto = He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO::make([
        'title' => 'Dev Backend', 'department_id' => $dept->id, 'team_id' => $team->id,
        'recruiter_id' => $recruiter->id, 'description' => 'desc',
        'experience_level' => He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum::Senior,
        'employment_type' => null,
        'work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::PartTime,
        'work_arrangement' => He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum::Remote,
        'status' => He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum::Draft,
        'summary' => 'sum', 'created_by' => $user->id, 'items' => [],
    ]);

    $req = resolve(He4rt\Recruitment\Requisitions\Actions\StoreJobRequisitionAction::class)->execute($dto);

    expect($req->fresh()->work_schedule)->toBe(He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::PartTime)
        ->and($req->fresh()->employment_type)->toBeNull();
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="persists work_schedule"`
Expected: FAIL — `work_schedule` não gravado.

- [ ] **Step 3: Atualizar `StoreJobRequisitionAction`**

In `app-modules/recruitment/src/Requisitions/Actions/StoreJobRequisitionAction.php`, no array do `JobRequisition::query()->create([...])`, após `'employment_type' => $dto->employmentType,` adicionar:

```php
                'work_schedule' => $dto->workSchedule,
```

- [ ] **Step 4: Atualizar `GenerateJobRequisition`**

In `app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisition.php`:

No bloco PROMPT, trocar a linha `- Tipo de contratação: {$dto->employmentType->value}` por:

```php
                        - Tipo de contratação (regime): {$dto->employmentType?->value}
                        - Jornada de trabalho: {$dto->workSchedule?->value}
```

No `JobRequisitionDTO::make([...])`, após `'employment_type' => $dto->employmentType,` adicionar:

```php
                'work_schedule' => $dto->workSchedule,
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="persists work_schedule"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment/src/Requisitions/Actions/StoreJobRequisitionAction.php \
        app-modules/recruitment/src/Requisitions/Actions/AiJobRequisition/GenerateJobRequisition.php \
        app-modules/recruitment/tests/Feature/JobRequisitionTest.php
git commit -m "feat(recruitment): persist work_schedule and pass it through AI generation"
```

---

## Task 7: Formulários Filament (3 painéis + ação IA)

**Context:** Quatro schemas oferecem `Select employment_type` com `->required()` e nenhum oferece jornada. Tornar `employment_type` opcional e adicionar `work_schedule`. panel-admin/panel-app usam `Select`; panel-organization e a ação IA usam `He4rtSelect`.

**Files:**
- Modify: `app-modules/panel-admin/src/Filament/Resources/Recruitment/JobRequisitions/Schemas/JobRequisitionForm.php` (`:132-135`)
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Schemas/JobRequisitionForm.php` (`:153-160`)
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Actions/GenerateJobRequisitionAction.php` (`:180-187`, `:59-60`)
- Modify: `app-modules/panel-app/src/Filament/Resources/JobRequisitions/Schemas/JobRequisitionForm.php` (`:33-35`)
- Modify: `app-modules/recruitment/lang/{en,pt_BR}/filament.php` (`:122-124`)
- Test: `app-modules/panel-admin/tests/Feature/Filament/JobRequisition/CreateJobRequisitionTest.php` (caso novo)

**Comportamento esperado (BDD):**
- Given o form de criação no panel-admin, When submeto sem `employment_type` mas com `work_schedule`, Then não há erro de validação e a vaga é criada com `employment_type=NULL`.
- Given o form, Then existe um `Select work_schedule` com as 4 opções.

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/panel-admin/tests/Feature/Filament/JobRequisition/CreateJobRequisitionTest.php` (seguir o padrão de auth/setup já existente no arquivo para `$this->actingAs(...)`):

```php
it('creates a requisition with work_schedule and without employment_type', function (): void {
    $form = Livewire::test(
        He4rt\PanelAdmin\Filament\Resources\Recruitment\JobRequisitions\Pages\CreateJobRequisition::class
    );

    $form->assertFormFieldExists('work_schedule');
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="creates a requisition with work_schedule"`
Expected: FAIL — campo `work_schedule` inexistente.

- [ ] **Step 3: panel-admin form**

In `app-modules/panel-admin/.../JobRequisitions/Schemas/JobRequisitionForm.php`, adicionar import `use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;` e substituir o bloco do `employment_type` (`:132-135`) por:

```php
                    Select::make('employment_type')
                        ->label(__('recruitment::filament.requisition.fields.employment_type'))
                        ->options(EmploymentTypeEnum::class)
                        ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
                    Select::make('work_schedule')
                        ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                        ->options(WorkScheduleEnum::class)
                        ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
```

- [ ] **Step 4: panel-app form**

In `app-modules/panel-app/.../JobRequisitions/Schemas/JobRequisitionForm.php`, adicionar import do `WorkScheduleEnum` e substituir o bloco `employment_type` (`:33-35`) por:

```php
                Select::make('employment_type')
                    ->options(EmploymentTypeEnum::class),
                Select::make('work_schedule')
                    ->options(WorkScheduleEnum::class),
```

- [ ] **Step 5: panel-organization form**

In `app-modules/panel-organization/.../JobRequisitions/Schemas/JobRequisitionForm.php`, adicionar import do `WorkScheduleEnum` e substituir o bloco `employment_type` (`:153-160`) por:

```php
                                        He4rtSelect::make('employment_type')
                                            ->label(__('recruitment::filament.requisition.fields.employment_type'))
                                            ->options(EmploymentTypeEnum::class)
                                            ->description(__('recruitment::filament.requisition.fields.employment_type_description'))
                                            ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
                                        He4rtSelect::make('work_schedule')
                                            ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                                            ->options(WorkScheduleEnum::class)
                                            ->description(__('recruitment::filament.requisition.fields.work_schedule_description'))
                                            ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
```

(Manter as demais chamadas encadeadas que existirem entre `:156-160` que não sejam `->required()`; remover apenas `->required()`.)

- [ ] **Step 6: ação IA panel-organization**

In `app-modules/panel-organization/.../JobRequisitions/Actions/GenerateJobRequisitionAction.php`:

Adicionar import do `WorkScheduleEnum`. Substituir o bloco `employment_type` (`:180-187`) por:

```php
                            He4rtSelect::make('employment_type')
                                ->label(__('recruitment::filament.requisition.fields.employment_type'))
                                ->options(EmploymentTypeEnum::class)
                                ->description(__('recruitment::filament.requisition.fields.employment_type_description')),
                            He4rtSelect::make('work_schedule')
                                ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                                ->options(WorkScheduleEnum::class)
                                ->description(__('recruitment::filament.requisition.fields.work_schedule_description')),
```

E no array de dados (`:59-60`), após `'employment_type' => $data['employment_type']->value,` trocar para tolerar null e adicionar jornada:

```php
                    'employment_type' => $data['employment_type']?->value,
                    'work_schedule' => $data['work_schedule']?->value,
```

- [ ] **Step 7: i18n dos campos**

In `app-modules/recruitment/lang/pt_BR/filament.php`, no grupo onde estão `:122-124` (`requisition.fields`), adicionar:

```php
            'work_schedule' => 'Jornada de Trabalho',
            'work_schedule_description' => 'Carga horária da posição',
            'not_specified' => 'Não informado',
```

In `app-modules/recruitment/lang/en/filament.php`, no mesmo grupo:

```php
            'work_schedule' => 'Work Schedule',
            'work_schedule_description' => 'Working hours of the position',
            'not_specified' => 'Not specified',
```

- [ ] **Step 8: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="creates a requisition with work_schedule"`
Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add app-modules/panel-admin app-modules/panel-organization app-modules/panel-app \
        app-modules/recruitment/lang/en/filament.php app-modules/recruitment/lang/pt_BR/filament.php
git commit -m "feat(panels): add work_schedule field and make employment_type optional in forms"
```

---

## Task 8: Tabelas e Infolists (3 painéis) — coluna `work_schedule` null-safe

**Context:** As `JobRequisitionsTable` dos 3 painéis (e infolists onde houver) exibem `employment_type`. Adicionar `work_schedule` e garantir placeholder "Não informado" para `NULL` em ambos.

**Files:**
- Modify: `app-modules/panel-admin/.../JobRequisitions/Tables/JobRequisitionsTable.php`
- Modify: `app-modules/panel-organization/.../JobRequisitions/Tables/JobRequisitionsTable.php`
- Modify: `app-modules/panel-app/.../JobRequisitions/Tables/JobRequisitionsTable.php`
- Modify: `app-modules/panel-organization/.../JobRequisitions/Schemas/JobRequisitionInfolist.php`
- Modify: `app-modules/panel-app/.../JobRequisitions/Schemas/JobRequisitionInfolist.php`
- Test: `app-modules/panel-admin/tests/Feature/Filament/JobRequisition/EditJobRequisitionTest.php` (caso novo)

**Comportamento esperado (BDD):**
- Given uma vaga com `employment_type=NULL`, When abro a tabela no panel-admin, Then a célula mostra "Não informado" (não quebra).
- Given a tabela, Then existe coluna `work_schedule`.

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/panel-admin/tests/Feature/Filament/JobRequisition/EditJobRequisitionTest.php`:

```php
it('lists requisitions with null employment_type without breaking', function (): void {
    He4rt\Recruitment\Requisitions\Models\JobRequisition::factory()->create([
        'employment_type' => null,
        'work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::FullTime,
    ]);

    Livewire::test(He4rt\PanelAdmin\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions::class)
        ->assertOk();
});
```

- [ ] **Step 2: Rodar e confirmar falha (ou erro)**

Run: `php artisan test --compact --filter="lists requisitions with null employment_type"`
Expected: FAIL — coluna `work_schedule` ausente / render quebra com null.

- [ ] **Step 3: Em cada `JobRequisitionsTable.php`**, localizar a coluna `TextColumn::make('employment_type')` e:
  1. Adicionar `->placeholder(__('recruitment::filament.requisition.fields.not_specified'))` nela.
  2. Logo após, adicionar a coluna de jornada:

```php
                TextColumn::make('work_schedule')
                    ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                    ->badge()
                    ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
```

- [ ] **Step 4: Em cada `JobRequisitionInfolist.php`**, localizar a entry de `employment_type` e:
  1. Adicionar `->placeholder(__('recruitment::filament.requisition.fields.not_specified'))`.
  2. Logo após, adicionar:

```php
                TextEntry::make('work_schedule')
                    ->label(__('recruitment::filament.requisition.fields.work_schedule'))
                    ->badge()
                    ->placeholder(__('recruitment::filament.requisition.fields.not_specified')),
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="lists requisitions with null employment_type"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/panel-admin app-modules/panel-organization app-modules/panel-app
git commit -m "feat(panels): show work_schedule column and null-safe employment_type"
```

---

## Task 9: Blade — null-safe + exibir jornada

**Context:** `job-description.blade.php:62-63` chama `employment_type->getIcon()/getLabel()` **sem null-safe** (quebra com `NULL`). `job-card.blade.php:65` tem fallback hardcoded `'Full Time'`.

**Files:**
- Modify: `app-modules/panel-app/resources/views/components/jobs/job-description.blade.php` (`:61-64`)
- Modify: `app-modules/panel-app/resources/views/components/jobs/job-card.blade.php` (`:65`)
- Test: `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php` (caso novo) — ou smoke test existente.

**Comportamento esperado (BDD):**
- Given uma vaga publicada com `employment_type=NULL`, When renderizo a página de descrição, Then não há exceção e aparece "Não informado".
- Given `work_schedule='full_time'`, Then a tag de jornada aparece com o label "Tempo Integral".

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php` (usar helper de criação de vaga publicada já presente no arquivo; se não houver, criar via factory com `status` publicado e `post`):

```php
it('renders job description with null employment_type and a work_schedule', function (): void {
    $req = He4rt\Recruitment\Requisitions\Models\JobRequisition::factory()
        ->has(He4rt\Recruitment\Requisitions\Models\JobPosting::factory(), 'post')
        ->create([
            'status' => He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum::Published,
            'employment_type' => null,
            'work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::FullTime,
        ]);

    $this->get('/'.$req->post->slug)->assertOk()->assertSee('Não informado');
});
```

(Ajustar a rota conforme o resolver de slug usado no projeto; se o teste de páginas já tiver um helper de URL, reutilizá-lo.)

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="renders job description with null employment_type"`
Expected: FAIL — `Call to a member function getIcon() on null`.

- [ ] **Step 3: Corrigir `job-description.blade.php`**

Substituir o bloco `{{-- Contract Type --}}` (`:61-64`) por:

```blade
                        {{-- Contract Type --}}
                        @if ($jobRequisition->employment_type)
                            <x-he4rt::tag :icon="$jobRequisition->employment_type->getIcon()" variant="ghost">
                                {{ $jobRequisition->employment_type->getLabel() }}
                            </x-he4rt::tag>
                        @endif

                        {{-- Work Schedule --}}
                        @if ($jobRequisition->work_schedule)
                            <x-he4rt::tag :icon="$jobRequisition->work_schedule->getIcon()" variant="ghost">
                                {{ $jobRequisition->work_schedule->getLabel() }}
                            </x-he4rt::tag>
                        @endif
```

- [ ] **Step 4: Corrigir `job-card.blade.php:65`**

Trocar `{{ $job->employment_type?->getLabel() ?? 'Full Time' }}` por:

```blade
            {{ $job->employment_type?->getLabel() ?? $job->work_schedule?->getLabel() ?? __('recruitment::filament.requisition.fields.not_specified') }}
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="renders job description with null employment_type"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/panel-app/resources/views/components/jobs/job-description.blade.php \
        app-modules/panel-app/resources/views/components/jobs/job-card.blade.php \
        app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php
git commit -m "fix(panel-app): null-safe employment_type and show work_schedule in job views"
```

---

## Task 10: Busca e Recomendações — filtro de jornada

**Context:** `SearchJobs` filtra `whereIn('employment_type', ...)` (`:83`); `JobRecommendations` idem (`:46`). Adicionar filtro paralelo `whereIn('work_schedule', ...)` em `SearchJobs`. `JobRecommendations` mantém só regime (recomendação por tipo) — sem mudança funcional, apenas garantir que `employment_type=NULL` não quebra (já é `whereIn`, então NULL simplesmente não casa: comportamento aceito pelo spec).

**Files:**
- Modify: `app-modules/panel-app/src/Livewire/SearchJobs.php` (`:36-90`)
- Test: `app-modules/panel-app/tests/Feature/Livewire/SearchJobsTest.php` (caso novo)

**Comportamento esperado (BDD):**
- Given vagas com `work_schedule` `full_time` e `part_time`, When filtro `workSchedules=['part_time']`, Then só a vaga `part_time` aparece.
- Given uma vaga com `employment_type=NULL`, When não há filtro de regime, Then ela aparece normalmente.

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/panel-app/tests/Feature/Livewire/SearchJobsTest.php` (seguir setup de auth/publicação já usado no arquivo):

```php
it('filters jobs by work_schedule', function (): void {
    $ft = publishedJob(['work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::FullTime]);
    $pt = publishedJob(['work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::PartTime]);

    Livewire::test(He4rt\PanelApp\Livewire\SearchJobs::class)
        ->set('workSchedules', ['part_time'])
        ->assertViewHas('jobs', fn ($jobs) => $jobs->pluck('id')->contains($pt->id)
            && ! $jobs->pluck('id')->contains($ft->id));
});
```

(Usar o helper `publishedJob()` já existente no arquivo de teste; se não existir, criar via factory publicada com `post` e `stages`, conforme `jobs()` exige `hasStages()`/`publicJobs()`.)

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="filters jobs by work_schedule"`
Expected: FAIL — propriedade `workSchedules` inexistente.

- [ ] **Step 3: Adicionar a propriedade e o filtro em `SearchJobs`**

Adicionar import `use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;`. Após a propriedade `$employmentTypes` (`:42`):

```php
    /**
     * @var array<int, WorkScheduleEnum|string>
     */
    #[Url]
    public array $workSchedules = [];
```

No builder `jobs()`, logo após o `->when($this->employmentTypes, ...)`:

```php
            ->when($this->workSchedules, function ($query): void {
                $query->whereIn('work_schedule', $this->workSchedules);
            })
```

Se existir um método `updatingEmploymentTypes()` (`:119`) que reseta paginação, adicionar o análogo:

```php
    public function updatingWorkSchedules(): void
    {
        $this->resetPage();
    }
```

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="filters jobs by work_schedule"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app-modules/panel-app/src/Livewire/SearchJobs.php \
        app-modules/panel-app/tests/Feature/Livewire/SearchJobsTest.php
git commit -m "feat(panel-app): add work_schedule filter to job search"
```

---

## Task 11: Factory + onboarding i18n

**Context:** `JobRequisitionFactory::getRealisticEmploymentType()` (`:128-133`) retorna `EmploymentTypeEnum::FullTimeEmployee` (não existe mais). Reescrever e adicionar jornada. Onboarding `employment_type_interests` (texto livre) tem placeholder com `full_time_employee` — só ajuste cosmético de i18n.

**Files:**
- Modify: `app-modules/recruitment/database/factories/JobRequisitionFactory.php` (`:43-44`, `:128-133`)
- Modify: `app-modules/panel-app/lang/{en,pt_BR}/pages/onboarding.php` (`:124-126`)

**Comportamento esperado (BDD):**
- Given `JobRequisition::factory()->create()`, Then `employment_type` ∈ {`clt`,`contractor`,`temporary`,`freelancer`} ou `null`, e `work_schedule` ∈ casos de `WorkScheduleEnum` ou `null` — sempre valores válidos.

- [ ] **Step 1: Escrever o teste que falha**

Append em `app-modules/recruitment/tests/Feature/JobRequisitionTest.php`:

```php
it('factory produces valid employment_type and work_schedule', function (): void {
    He4rt\Recruitment\Requisitions\Models\JobRequisition::factory()->count(20)->create()->each(function ($r): void {
        expect($r->employment_type === null || $r->employment_type instanceof He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum)->toBeTrue()
            ->and($r->work_schedule === null || $r->work_schedule instanceof He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum)->toBeTrue();
    });
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="factory produces valid"`
Expected: FAIL — `EmploymentTypeEnum::FullTimeEmployee` não existe (erro fatal na factory).

- [ ] **Step 3: Reescrever os helpers da factory**

In `app-modules/recruitment/database/factories/JobRequisitionFactory.php`, adicionar import `use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;`. No array `definition()`, trocar a linha `'employment_type' => $this->getRealisticEmploymentType(),` por:

```php
            'employment_type' => $this->getRealisticEmploymentType(),
            'work_schedule' => $this->getRealisticWorkSchedule(),
```

Substituir o método `getRealisticEmploymentType()` (`:128-133`) por:

```php
    private function getRealisticEmploymentType(): ?EmploymentTypeEnum
    {
        return fake()->boolean(85)
            ? EmploymentTypeEnum::Clt
            : fake()->randomElement([...EmploymentTypeEnum::cases(), null]);
    }

    private function getRealisticWorkSchedule(): ?WorkScheduleEnum
    {
        return fake()->boolean(80)
            ? WorkScheduleEnum::FullTime
            : fake()->randomElement([...WorkScheduleEnum::cases(), null]);
    }
```

- [ ] **Step 4: Ajustar onboarding i18n**

In `app-modules/panel-app/lang/pt_BR/pages/onboarding.php` (`:124-126`), trocar o placeholder/helper:

```php
                'employment_type_interests' => 'Tipos de Contratação (separados por vírgula)',
                'employment_type_interests_placeholder' => 'clt, contractor, temporary',
                'employment_type_interests_helper' => 'CLT, Contrato/PJ, Temporário, etc.',
```

In `app-modules/panel-app/lang/en/pages/onboarding.php` (chaves equivalentes):

```php
                'employment_type_interests' => 'Employment Types (comma-separated)',
                'employment_type_interests_placeholder' => 'clt, contractor, temporary',
                'employment_type_interests_helper' => 'CLT, Contractor/PJ, Temporary, etc.',
```

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter="factory produces valid"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app-modules/recruitment/database/factories/JobRequisitionFactory.php \
        app-modules/panel-app/lang/en/pages/onboarding.php \
        app-modules/panel-app/lang/pt_BR/pages/onboarding.php \
        app-modules/recruitment/tests/Feature/JobRequisitionTest.php
git commit -m "feat(recruitment): update factory and onboarding copy for split taxonomy"
```

---

## Task 12: Regressão + suíte completa + Pint

**Context:** Mudança ampla (~20 arquivos, 5 módulos). Rodar a suíte dos módulos afetados, garantir que `WorkArrangementEnum`/`ExperienceLevelEnum` não foram tocados, e formatar.

**Files:**
- Test: nenhum novo — execução agregada.

- [ ] **Step 1: Teste de regressão dos enums intocados**

Append em `app-modules/recruitment/tests/Unit/WorkScheduleEnumTest.php`:

```php
it('does not alter WorkArrangement or ExperienceLevel enums', function (): void {
    expect(array_map(fn ($c) => $c->value, He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum::cases()))
        ->toBe(['remote', 'hybrid', 'on_site'])
        ->and(He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum::tryFrom('trainee'))
        ->not->toBeNull();
});
```

- [ ] **Step 2: Rodar a suíte dos módulos afetados**

Run:
```bash
php artisan test --compact app-modules/recruitment/tests
php artisan test --compact app-modules/panel-admin/tests/Feature/Filament/JobRequisition
php artisan test --compact app-modules/panel-organization/tests/Feature/JobRequisition
php artisan test --compact app-modules/panel-app/tests/Feature/Livewire app-modules/panel-app/tests/Feature/Filament/JobRequisitions
php artisan test --compact app-modules/candidates/tests/Feature/CandidateOnboardingTest.php
```
Expected: todos PASS. Se algum teste legado referenciar `EmploymentTypeEnum::FullTimeEmployee`/`Intern`/`PartTime` ou string `full_time_employee`, atualizá-lo para a nova taxonomia (substituir por `Clt`/`Contractor`/etc. ou mover a expectativa para `work_schedule`), commitando junto.

- [ ] **Step 3: Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem erros remanescentes.

- [ ] **Step 4: Commit final**

```bash
git add -A
git commit -m "test(recruitment): regression for untouched enums and full-suite green after split"
```

---

## Notas de execução

- **Ordem obrigatória:** Tasks 1→2→3 são fundação (enum, enum, schema). 4→6 dependem delas. 7→11 são consumidores (paralelizáveis entre si após 6). 12 por último.
- **Tasks legadas que podem quebrar:** qualquer teste/factory/seeder citando `full_time_employee`, `EmploymentTypeEnum::FullTimeEmployee`, `EmploymentTypeEnum::Intern`, `EmploymentTypeEnum::PartTime`. A Task 12 Step 2 captura e corrige.
- **Risco residual aceito (spec §Side effects):** `employment_type`/`work_schedule` `NULL` não aparecem em filtros `whereIn` ("Não informado" não é filtrável) — comportamento intencional.
- **Heroicon::ClockSolid:** se o enum `Heroicon` do Filament não tiver `ClockSolid`, usar `Heroicon::Clock` para `PartTime` também (ajuste trivial detectado ao rodar Task 1).
