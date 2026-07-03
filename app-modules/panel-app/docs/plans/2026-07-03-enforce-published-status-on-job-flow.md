# Enforce Published Status on Job View and Application Flow — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Impedir que vagas não publicadas sejam visualizadas ou recebam candidatura por acesso direto ao link, aplicando `status === Published` como defesa em profundidade na view e na ação de domínio.

**Architecture:** Introduz um helper único `JobRequisition::isPublished()` (fonte de verdade), reusado por um guard de UX em `ViewJobRequisition::mount()` (redirect + aviso), por um guard de domínio em `ApplyToJobRequisitionAction::execute()` (lança `RequisitionNotPublishedException`) e pelo `JobApplyIntentController` do #225 (consistência). O predicado é **somente status**, nunca `publicJobs()`, para não quebrar vagas internas.

**Tech Stack:** Laravel 12, Filament v5, Livewire v4, Pest v4, PHP 8.4, PostgreSQL. Módulos: `recruitment`, `applications`, `panel-app`.

## Global Constraints

- **NÃO commitar.** O usuário faz os commits manualmente. Onde o passo diz "Checkpoint", rode os checks de qualidade e **pare** — não rode `git commit`/`git push`.
- **Predicado = `status === RequisitionStatusEnum::Published`**, apenas. Nunca incluir `publicJobs()`/`is_internal_only` no guard (quebraria vaga interna).
- **UX de vaga não publicada na view = redirect para a listagem + notificação de aviso**, reusando a chave `panel-app::filament.pages.job_description.job_unavailable` (já existe em `en` e `pt_BR`).
- **Precedência na `mount()`:** o check "já candidatado" vem **antes** do check de status.
- **i18n:** nenhuma chave nova é necessária (reuso de `job_unavailable`).
- **Pint** após editar PHP: `vendor/bin/pint --dirty --format agent`.
- **PHPStan** por módulo tocado: `vendor/bin/phpstan analyse --memory-limit=1G`.
- **Armadilha `UserObserver`:** `User::factory()->create()` devolve `$user->candidate` como `null` (cache). Nos testes, use `$user->candidate()->update([...])` + `$user->refresh()`. Nunca crie um segundo Candidate para o mesmo user.
- **Armadilha factory:** `JobRequisitionFactory` gera `status` aleatório por default. Todo teste que espera uma vaga acessível deve fixar `status => Published` explicitamente.

---

## File Structure

| Arquivo                                                                                         | Responsabilidade                        | Ação      |
| ----------------------------------------------------------------------------------------------- | --------------------------------------- | --------- |
| `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php`                            | Helper `isPublished(): bool`            | Modificar |
| `app-modules/recruitment/tests/Unit/JobRequisitionIsPublishedTest.php`                          | Unit test do helper                     | Criar     |
| `app-modules/applications/src/Exceptions/RequisitionNotPublishedException.php`                  | Exceção de domínio                      | Criar     |
| `app-modules/applications/src/Actions/ApplyToJobRequisitionAction.php`                          | Guard de domínio                        | Modificar |
| `app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php`            | Testes da ação (+ fix flaky)            | Modificar |
| `app-modules/panel-app/src/Filament/Resources/JobRequisitions/Pages/ViewJobRequisition.php`     | Guard de UX na `mount()`                | Modificar |
| `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php` | Testes do guard de status na view       | Criar     |
| `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php`      | Fix: fixar `Published` nas requisitions | Modificar |
| `app-modules/panel-app/src/Http/Controllers/JobApplyIntentController.php`                       | Usar `isPublished()` (consistência)     | Modificar |

---

## Task 1: `JobRequisition::isPublished()` (recruitment)

**Files:**

- Modify: `app-modules/recruitment/src/Requisitions/Models/JobRequisition.php` (inserir após `applicationFrom()`, linha ~174)
- Test: `app-modules/recruitment/tests/Unit/JobRequisitionIsPublishedTest.php` (criar)

**Interfaces:**

- Consumes: `RequisitionStatusEnum` (já importado no model, linha 16).
- Produces: `JobRequisition::isPublished(): bool` — usado nas Tasks 2, 3 e 4.

- [ ] **Step 1: Escrever o teste que falha**

Criar `app-modules/recruitment/tests/Unit/JobRequisitionIsPublishedTest.php` (segue o estilo de `JobRequisitionSalaryRangeTest.php`: model puro, sem DB):

```php
<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('is published only when status is Published', function (RequisitionStatusEnum $status, bool $expected): void {
    $job = new JobRequisition();
    $job->status = $status;

    expect($job->isPublished())->toBe($expected);
})->with([
    'draft' => [RequisitionStatusEnum::Draft, false],
    'pending approval' => [RequisitionStatusEnum::PendingApproval, false],
    'approved' => [RequisitionStatusEnum::Approved, false],
    'published' => [RequisitionStatusEnum::Published, true],
    'on hold' => [RequisitionStatusEnum::OnHold, false],
    'closed' => [RequisitionStatusEnum::Closed, false],
    'cancelled' => [RequisitionStatusEnum::Cancelled, false],
]);
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test app-modules/recruitment/tests/Unit/JobRequisitionIsPublishedTest.php --compact`
Expected: FAIL — `Call to undefined method He4rt\Recruitment\Requisitions\Models\JobRequisition::isPublished()`.

- [ ] **Step 3: Implementar o método**

Em `JobRequisition.php`, inserir logo após o método `applicationFrom()` (que termina na linha ~174), antes de `getNextStage()`:

```php
    /**
     * Whether the requisition is live (visible and accepting applications).
     *
     * Single source of truth for the published check, shared by the job view
     * guard, the apply action guard, and the apply-intent controller. Scoped to
     * status only — never `publicJobs()` — so internal-only published jobs stay
     * reachable by direct link.
     */
    public function isPublished(): bool
    {
        return $this->status === RequisitionStatusEnum::Published;
    }
```

- [ ] **Step 4: Rodar o teste e confirmar que passa**

Run: `php artisan test app-modules/recruitment/tests/Unit/JobRequisitionIsPublishedTest.php --compact`
Expected: PASS (7 casos).

- [ ] **Step 5: Checkpoint (sem commit)**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --memory-limit=1G
```

Expected: Pint sem alterações pendentes relevantes; PHPStan sem erros novos. **NÃO commitar.**

---

## Task 2: `RequisitionNotPublishedException` + guard na ação (applications)

**Files:**

- Create: `app-modules/applications/src/Exceptions/RequisitionNotPublishedException.php`
- Modify: `app-modules/applications/src/Actions/ApplyToJobRequisitionAction.php`
- Test: `app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php` (adicionar casos + corrigir o existente)

**Interfaces:**

- Consumes: `JobRequisition::isPublished()` (Task 1); `JobRequisition` (recruitment).
- Produces: `RequisitionNotPublishedException::cannotApplyToRequisition(JobRequisition $requisition): self` (código HTTP 422); guard em `ApplyToJobRequisitionAction::execute()` que lança essa exceção antes de criar a `Application`.

- [ ] **Step 1: Escrever os testes que falham (novos casos + fix do existente)**

Editar `ApplyToJobRequisitionActionTest.php`. **(a)** Adicionar imports no topo (após os imports existentes):

```php
use He4rt\Applications\Exceptions\RequisitionNotPublishedException;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;

use function Pest\Laravel\assertDatabaseCount;
```

**(b)** Corrigir o teste existente (linha ~20) para fixar `Published` — hoje a factory sorteia status e, com o guard, o teste ficaria intermitente:

```php
// ANTES
$requisition = JobRequisition::factory()->create();

// DEPOIS
$requisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);
```

**(c)** Adicionar dois novos testes ao final do arquivo:

```php
it('throws and creates no application when the requisition is not published', function (RequisitionStatusEnum $status): void {
    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create(['status' => $status]);

    expect(fn () => resolve(ApplyToJobRequisitionAction::class)->execute($requisition, $candidate))
        ->toThrow(RequisitionNotPublishedException::class);

    assertDatabaseCount(Application::class, 0);
})->with([
    'draft' => [RequisitionStatusEnum::Draft],
    'closed' => [RequisitionStatusEnum::Closed],
    'cancelled' => [RequisitionStatusEnum::Cancelled],
]);

it('creates the application when the requisition is published', function (): void {
    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);

    $application = resolve(ApplyToJobRequisitionAction::class)->execute($requisition, $candidate);

    assertDatabaseHas(Application::class, [
        'id' => $application->getKey(),
        'requisition_id' => $requisition->getKey(),
    ]);
});
```

- [ ] **Step 2: Rodar os testes e confirmar que falham**

Run: `php artisan test app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php --compact`
Expected: FAIL — `Class "He4rt\Applications\Exceptions\RequisitionNotPublishedException" not found` (a exceção ainda não existe).

- [ ] **Step 3: Criar a exceção de domínio**

Criar `app-modules/applications/src/Exceptions/RequisitionNotPublishedException.php` (mesmo padrão de `InvalidTransitionException`/`MissingTransitionDataException`):

```php
<?php

declare(strict_types=1);

namespace He4rt\Applications\Exceptions;

use Exception;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

final class RequisitionNotPublishedException extends Exception
{
    public static function cannotApplyToRequisition(JobRequisition $requisition): self
    {
        return new self(sprintf(
            'Cannot apply to requisition %s: status is %s, not Published.',
            $requisition->getKey(),
            $requisition->status->value,
        ), 422);
    }
}
```

- [ ] **Step 4: Adicionar o guard na ação**

Em `ApplyToJobRequisitionAction.php`, adicionar o import e o guard no início de `execute()`, antes do `Application::query()->create(...)`:

```php
use He4rt\Applications\Exceptions\RequisitionNotPublishedException;
```

```php
    public function execute(
        JobRequisition $requisition,
        Candidate $candidate,
        CandidateSourceEnum $source = CandidateSourceEnum::CareerPage,
    ): Application {
        if (! $requisition->isPublished()) {
            throw RequisitionNotPublishedException::cannotApplyToRequisition($requisition);
        }

        $application = Application::query()->create([
            // ... (inalterado)
        ]);
```

- [ ] **Step 5: Rodar os testes e confirmar que passam**

Run: `php artisan test app-modules/applications/tests/Feature/Actions/ApplyToJobRequisitionActionTest.php --compact`
Expected: PASS (teste original + 3 casos de exceção + 1 de published).

- [ ] **Step 6: Rodar os testes de listeners que usam a ação**

A ação é usada em `SendApplicationReceivedNotificationTest`. Garantir que não regrediram:

Run: `php artisan test app-modules/applications/tests/Feature/Listeners/SendApplicationReceivedNotificationTest.php --compact`
Expected: PASS. Se falhar por status aleatório, fixar `['status' => RequisitionStatusEnum::Published]` na criação da requisition daquele teste (mesmo fix do Step 1b).

- [ ] **Step 7: Checkpoint (sem commit)**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --memory-limit=1G
```

Expected: sem erros. **NÃO commitar.**

---

## Task 3: Guard de UX em `ViewJobRequisition::mount()` (panel-app)

**Files:**

- Modify: `app-modules/panel-app/src/Filament/Resources/JobRequisitions/Pages/ViewJobRequisition.php`
- Create: `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php`
- Modify: `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/JobRequisitionPagesTest.php` (fixar `Published`)

**Interfaces:**

- Consumes: `JobRequisition::isPublished()` (Task 1); `JobRequisitionResource::getUrl('index')` → rota `filament.app.resources.vagas.index`; chave i18n `panel-app::filament.pages.job_description.job_unavailable`.
- Produces: comportamento de redirect+aviso para vaga não publicada, com precedência do check "já candidatado".

- [ ] **Step 1: Escrever o teste de status que falha (arquivo novo)**

Criar `app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php`. Usa HTTP `get()` (consistente com `JobApplyIntentTest`, confiável para redirect+notificação):

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function makeRequisition(array $attributes = []): JobRequisition
{
    $team = Team::factory()->create();

    return JobRequisition::factory()
        ->for($team)
        ->for(Department::factory()->for($team))
        ->for(Recruiter::factory()->for($team), 'recruiter')
        ->for(User::factory(), 'createdBy')
        ->create(array_merge([
            'is_confidential' => false,
            'is_internal_only' => false,
            'status' => RequisitionStatusEnum::Published,
        ], $attributes));
}

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders a published job', function (): void {
    $posting = JobPosting::factory()->for(makeRequisition(), 'jobRequisition')->create();

    get(route('filament.app.resources.vagas.view', ['record' => $posting->slug]))
        ->assertOk();
});

it('renders an internal-only published job by direct link (regression)', function (): void {
    $requisition = makeRequisition(['is_internal_only' => true]);
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    get(route('filament.app.resources.vagas.view', ['record' => $posting->slug]))
        ->assertOk();
});

it('redirects to the jobs list with a warning when the job is not published', function (RequisitionStatusEnum $status): void {
    $requisition = makeRequisition(['status' => $status]);
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    get(route('filament.app.resources.vagas.view', ['record' => $posting->slug]))
        ->assertRedirect(route('filament.app.resources.vagas.index'))
        ->assertSessionHas('filament.notifications');
})->with([
    'draft' => [RequisitionStatusEnum::Draft],
    'closed' => [RequisitionStatusEnum::Closed],
    'cancelled' => [RequisitionStatusEnum::Cancelled],
]);

it('redirects an already-applied candidate to their application even when the job is closed', function (): void {
    $requisition = makeRequisition(['status' => RequisitionStatusEnum::Closed]);
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $user = User::factory()->create();
    $user->candidate()->update(['is_onboarded' => true]);
    $user->refresh();

    $application = Application::factory()
        ->for($user->candidate)
        ->for($requisition, 'requisition')
        ->create();

    actingAs($user);

    get(route('filament.app.resources.vagas.view', ['record' => $posting->slug]))
        ->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));
});
```

- [ ] **Step 2: Rodar o teste e confirmar que falha**

Run: `php artisan test app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php --compact`
Expected: FAIL — os casos "not published" retornam 200 (a página abre) em vez de redirect, porque o guard ainda não existe.

- [ ] **Step 3: Implementar o guard na `mount()`**

Substituir o método `mount()` de `ViewJobRequisition.php` e adicionar o import de `Notification`:

```php
use Filament\Notifications\Notification;
```

```php
    public function mount(int|string $record): void
    {
        $requisitionId = JobPosting::query()->where('slug', $record)->firstOrFail()->job_requisition_id;

        parent::mount($requisitionId);

        /** @var User|null $user */
        $user = auth()->user();

        if ($user?->candidate) {
            $application = $this->record->applicationFrom($user->candidate);

            if ($application) {
                $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));

                return;
            }
        }

        if (! $this->record->isPublished()) {
            Notification::make()
                ->title(__('panel-app::filament.pages.job_description.job_unavailable'))
                ->warning()
                ->send();

            $this->redirect(JobRequisitionResource::getUrl('index'));

            return;
        }
    }
```

- [ ] **Step 4: Rodar o teste do arquivo novo e confirmar que passa**

Run: `php artisan test app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ViewJobRequisitionStatusTest.php --compact`
Expected: PASS (published, internal+published, 3× not-published redirect, already-applied precedence).

- [ ] **Step 5: Corrigir os testes existentes de `JobRequisitionPagesTest.php`**

O guard novo quebra os testes que criam requisitions com status aleatório e esperam `assertOk`/`assertSee`. Fixar `Published` em cada criação. Aplicar estas 5 edições:

1. `beforeEach` (linha ~33):

```php
// ANTES
->create(['is_confidential' => false, 'is_internal_only' => false]);
// DEPOIS
->create(['is_confidential' => false, 'is_internal_only' => false, 'status' => RequisitionStatusEnum::Published]);
```

2. "should hide company name for confidential jobs" (linha ~70):

```php
// ANTES
->create(['is_confidential' => true]);
// DEPOIS
->create(['is_confidential' => true, 'status' => RequisitionStatusEnum::Published]);
```

3. "should show confidential-about section" (linha ~94):

```php
// ANTES
->create(['is_confidential' => true]);
// DEPOIS
->create(['is_confidential' => true, 'status' => RequisitionStatusEnum::Published]);
```

4. "renders with null employment_type" (linha ~149-153):

```php
// ANTES
->create([
    'is_confidential' => false,
    'employment_type' => null,
    'work_schedule' => WorkScheduleEnum::FullTime,
]);
// DEPOIS
->create([
    'is_confidential' => false,
    'employment_type' => null,
    'work_schedule' => WorkScheduleEnum::FullTime,
    'status' => RequisitionStatusEnum::Published,
]);
```

5. "hides the share button for internal-only jobs" (linha ~176):

```php
// ANTES
->create(['is_confidential' => false, 'is_internal_only' => true]);
// DEPOIS
->create(['is_confidential' => false, 'is_internal_only' => true, 'status' => RequisitionStatusEnum::Published]);
```

E adicionar o import no topo do arquivo (se ainda não houver):

```php
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
```

- [ ] **Step 6: Rodar todo o diretório de testes de JobRequisitions do panel-app**

Run: `php artisan test app-modules/panel-app/tests/Feature/Filament/JobRequisitions/ --compact`
Expected: PASS (arquivo novo + `JobRequisitionPagesTest` corrigido).

- [ ] **Step 7: Checkpoint (sem commit)**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --memory-limit=1G
```

Expected: sem erros. **NÃO commitar.**

---

## Task 4: Consistência no `JobApplyIntentController` (panel-app)

**Files:**

- Modify: `app-modules/panel-app/src/Http/Controllers/JobApplyIntentController.php`
- Test: `app-modules/panel-app/tests/Feature/JobApplyIntentTest.php` (já existe; roda sem alterações para provar que o refactor não muda o comportamento)

**Interfaces:**

- Consumes: `JobRequisition::isPublished()` (Task 1).
- Produces: nenhuma nova; refatoração de paridade (mesmo comportamento observável).

- [ ] **Step 1: Confirmar baseline verde antes do refactor**

Run: `php artisan test app-modules/panel-app/tests/Feature/JobApplyIntentTest.php --compact`
Expected: PASS (7 testes). Este é o baseline: o comportamento não pode mudar.

- [ ] **Step 2: Refatorar o predicado para `isPublished()`**

Em `JobApplyIntentController.php`, trocar a comparação inline pelo helper e **remover** o import agora não usado de `RequisitionStatusEnum`:

```php
// ANTES
$isAvailable = $posting?->jobRequisition?->status === RequisitionStatusEnum::Published;

// DEPOIS
$isAvailable = $posting?->jobRequisition?->isPublished() ?? false;
```

Remover a linha de import:

```php
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
```

- [ ] **Step 3: Rodar o teste do controller e confirmar que segue verde**

Run: `php artisan test app-modules/panel-app/tests/Feature/JobApplyIntentTest.php --compact`
Expected: PASS (mesmos 7 testes; comportamento idêntico).

- [ ] **Step 4: Checkpoint (sem commit)**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --memory-limit=1G
```

Expected: sem erros (incluindo `no_unused_imports` limpo após remover o import). **NÃO commitar.**

---

## Verificação final (após todas as tasks, antes de qualquer push do usuário)

Espelha o `.husky/pre-push`, com o paralelismo limitado (máquina de 16 cores):

- [ ] `./vendor/bin/rector process --dry-run --ansi`
- [ ] `./vendor/bin/pint --test --ansi`
- [ ] `./vendor/bin/phpstan analyse --ansi`
- [ ] `nice -n 19 ./vendor/bin/pest --parallel --processes=10 --compact`

Todos verdes → o **usuário** decide sobre commit/push (não commitar automaticamente).
