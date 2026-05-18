# Ação "Mover etapa" do candidato — Implementation Plan (Abordagem B)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Criar uma nova `MoveStageAction` (select, forward-only, avaliação opcional via toggle) que passa pela máquina de estados, adicionada **ao lado** da `StateTransitionAction` existente (que é mantida intacta), e remover a página de edição de candidatura que burlava esse fluxo.

**Architecture:** Abordagem B (coexistência). `StateTransitionAction` NÃO é alterada nem removida. A nova `MoveStageAction` é adicionada ao `ApplicationInfolist` (Quick Actions) e ao `KanbanStages` (card actions), ao lado da antiga. Página de edição (`EditApplication`/`ApplicationForm`) removida. Sem migration.

**Tech Stack:** Laravel 12, Filament v5, Livewire v4, Pest v4, PHP 8.4, módulo `panel-organization`.

**Spec:** `docs/superpowers/specs/2026-05-18-mover-etapa-candidato-design.md` (Revisão 2)

**Aprendizados incorporados (de tentativa anterior):**

1. Ações embutidas em `Actions::make()` dentro de `Section`/`Grid` do infolist NÃO são alcançadas por `callAction('nome', ...)`. Testar com `TestAction::make('move-stage-action')->schemaComponent(true)`.
2. Visibilidade do `to_stage_id`: `$get('to_status')` pode chegar como string OU enum. Usar comparação normalizada por `.value` com `in_array(..., true)`. Cobrir com teste de visibilidade.

---

## File Structure

| Arquivo                                                                                                      | Responsabilidade                                                                                          |
| ------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------- |
| `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php` | **Novo.** Ação reutilizável: select status/etapa + notes + avaliação opcional → `current_step->handle()`. |
| `.../JobRequisitions/Pages/Kanban/Actions/StateTransitionAction.php`                                         | **Inalterado** (não tocar).                                                                               |
| `.../Applications/Schemas/ApplicationInfolist.php`                                                           | Adicionar `MoveStageAction` ao `Actions::make([...])`, mantendo `StateTransitionAction`.                  |
| `.../JobRequisitions/Pages/Kanban/KanbanStages.php`                                                          | Adicionar `MoveStageAction` aos `cardActions`, mantendo `StateTransitionAction`.                          |
| `.../Applications/ApplicationResource.php`                                                                   | Remover `canEdit()` e rota `'edit'`.                                                                      |
| `.../Applications/Tables/ApplicationsTable.php`                                                              | Remover `EditAction`.                                                                                     |
| `.../Applications/Pages/EditApplication.php`                                                                 | **Removido.**                                                                                             |
| `.../Applications/Schemas/ApplicationForm.php`                                                               | **Removido.**                                                                                             |
| `app-modules/applications/lang/{en,pt_BR}/filament.php`                                                      | Chaves `actions.move_stage.*`.                                                                            |
| `.../tests/Feature/Filament/Application/OwnerApplicationAccessTest.php`                                      | Remover testes do edit page.                                                                              |
| `.../tests/Feature/Filament/Application/MoveStageActionTest.php`                                             | **Novo.** Edge-cases + visibilidade + coexistência.                                                       |

---

## Task 1: Chaves de tradução `move_stage`

**Contexto:** A nova ação precisa de labels i18n (en + pt_BR). O bloco `actions` já existe em `app-modules/applications/lang/{en,pt_BR}/filament.php` (vizinho de `change_status`). Mudança puramente de dados.

**Files:**

- Modify: `app-modules/applications/lang/en/filament.php`
- Modify: `app-modules/applications/lang/pt_BR/filament.php`

- [ ] **Step 1: Adicionar bloco `move_stage` em `en/filament.php`**

No array `'actions' => [ ... ]`, logo após `'change_status' => [...]`, inserir:

```php
'move_stage' => [
    'label' => 'Move stage',
    'modal_heading' => 'Move candidate stage',
    'modal_submit' => 'Confirm',
    'no_transitions_tooltip' => 'No available transitions',
    'with_evaluation_label' => 'Record an evaluation for this move',
    'notifications' => [
        'moved' => [
            'title' => 'Candidate moved',
        ],
        'error' => [
            'title' => 'Could not move the candidate',
        ],
    ],
],
```

- [ ] **Step 2: Adicionar bloco `move_stage` em `pt_BR/filament.php`**

Mesmo lugar (após `'change_status'`), inserir:

```php
'move_stage' => [
    'label' => 'Mover etapa',
    'modal_heading' => 'Mover etapa do candidato',
    'modal_submit' => 'Confirmar',
    'no_transitions_tooltip' => 'Nenhuma transição disponível',
    'with_evaluation_label' => 'Registrar uma avaliação nesta movimentação',
    'notifications' => [
        'moved' => [
            'title' => 'Candidato movido',
        ],
        'error' => [
            'title' => 'Não foi possível mover o candidato',
        ],
    ],
],
```

- [ ] **Step 3: Validar sintaxe e commitar**

```bash
php -l app-modules/applications/lang/en/filament.php
php -l app-modules/applications/lang/pt_BR/filament.php
vendor/bin/pint --dirty --format agent
git add app-modules/applications/lang/en/filament.php app-modules/applications/lang/pt_BR/filament.php
git -c commit.gpgsign=false commit -m "feat(applications): add move_stage i18n keys"
```

---

## Task 2: `MoveStageAction` (transição, sem avaliação) + adicionar ao Infolist

**Contexto:** O `ApplicationInfolist.php` tem um `Actions::make([ StateTransitionAction::make(), CommentApplicationAction::make(), RejectApplicationAction::make() ])` na seção Quick Actions. **Adicionar** `MoveStageAction::make()` a essa lista, **sem remover** `StateTransitionAction`. Esta task cobre o caminho de transição sempre íntegro (status + etapa + notes → `current_step->handle()`), sem avaliação ainda (Task 3).

**Comportamento esperado (BDD):**

- **Dado** uma candidatura `InProgress` com etapa à frente **Quando** o admin executa `move-stage-action` com `to_status=InProgress` e `to_stage_id` **Então** `current_stage_id` vira a etapa alvo **E** um `ApplicationStageHistory` é criado.
- **Dado** a página do candidato **Então** `state-transition-action` (antiga) **e** `move-stage-action` (nova) coexistem.

**Antes** (`ApplicationInfolist.php`, dentro do `Actions::make([...])`):

```php
Actions::make([
    StateTransitionAction::make(),
    CommentApplicationAction::make(),
    RejectApplicationAction::make(),
]),
```

**Depois:**

```php
Actions::make([
    StateTransitionAction::make(),
    MoveStageAction::make(),
    CommentApplicationAction::make(),
    RejectApplicationAction::make(),
]),
```

(adicionar `use He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions\MoveStageAction;` mantendo a ordem alfabética; **manter** o `use ...StateTransitionAction;`)

**Files:**

- Create: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php`
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/ApplicationInfolist.php`
- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`

- [ ] **Step 1: Escrever o teste que falha**

Create `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::Admin->value);
    actingAs($this->admin);

    $this->team = Team::factory()->create(['owner_id' => $this->admin->id]);
    $this->application = Application::factory()
        ->withStatus(ApplicationStatusEnum::InProgress)
        ->create(['team_id' => $this->team->id]);

    JobPosting::factory()->for($this->application->requisition)->create();

    filament()->setTenant($this->team);
});

it('moves the candidate stage through the state machine without evaluation', function (): void {
    $targetStage = Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 999,
        'active' => true,
    ]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent(true),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'notes' => 'Avançando para a próxima fase.',
            ],
        )
        ->assertHasNoActionErrors();

    expect($this->application->fresh()->current_stage_id)->toBe($targetStage->id)
        ->and(ApplicationStageHistory::query()
            ->where('application_id', $this->application->id)
            ->where('to_stage_id', $targetStage->id)
            ->count())->toBe(1);
});

it('keeps the old StateTransitionAction alongside the new MoveStageAction', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionExists(TestAction::make('state-transition-action')->schemaComponent(true))
        ->assertActionExists(TestAction::make('move-stage-action')->schemaComponent(true));
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: FAIL — classe `MoveStageAction` inexistente.

- [ ] **Step 3: Criar `MoveStageAction.php`**

```php
<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use Illuminate\Support\Arr;

class MoveStageAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->outlined()
            ->label(__('applications::filament.actions.move_stage.label'))
            ->icon('heroicon-o-arrow-right-circle')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading(__('applications::filament.actions.move_stage.modal_heading'))
            ->modalSubmitActionLabel(__('applications::filament.actions.move_stage.modal_submit'))
            ->visible(fn (Application $record): bool => ! $record->is_last_stage)
            ->disabled(fn (Application $record): bool => ! $record->current_step->canChange() || $record->is_last_stage)
            ->tooltip(fn (Application $record): ?string => $record->current_step->canChange() ? null : __('applications::filament.actions.move_stage.no_transitions_tooltip'))
            ->schema($this->buildSchema(...))
            ->action($this->processAction(...))
            ->requiresConfirmation();
    }

    public static function getDefaultName(): ?string
    {
        return 'move-stage-action';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function processAction(Application $record, array $data): void
    {
        try {
            $transitionData = TransitionData::fromArray($data, auth()->id());
            $record->current_step->handle($transitionData);
        } catch (InvalidTransitionException|MissingTransitionDataException $e) {
            Notification::make()
                ->danger()
                ->title(__('applications::filament.actions.move_stage.notifications.error.title'))
                ->body($e->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title(__('applications::filament.actions.move_stage.notifications.moved.title'))
            ->send();
    }

    /** @return array<int, Field> */
    private function buildSchema(Application $record): array
    {
        $choices = Arr::except($record->current_step->choices(), [
            ApplicationStatusEnum::OfferAccepted->value,
            ApplicationStatusEnum::OfferDeclined->value,
            ApplicationStatusEnum::Hired->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::OfferExtended->value,
        ]);

        return [
            Select::make('to_status')
                ->label(__('applications::filament.fields.status'))
                ->options($choices)
                ->enum(ApplicationStatusEnum::class)
                ->required()
                ->live(),

            Select::make('to_stage_id')
                ->label(__('applications::filament.fields.current_stage'))
                ->options(fn () => ($record->requisition?->stages ?? collect()) // @phpstan-ignore nullsafe.neverNull
                    ->where('active', true)
                    ->where('display_order', '>', $record->currentStage?->display_order ?? 0) // @phpstan-ignore nullsafe.neverNull
                    ->pluck('name', 'id'))
                ->default($record->getNextStage()?->id)
                ->visible(function (Get $get): bool {
                    $status = $get('to_status');
                    $value = $status instanceof BackedEnum ? $status->value : (string) $status;

                    return in_array($value, [
                        ApplicationStatusEnum::InProgress->value,
                        ApplicationStatusEnum::OfferExtended->value,
                    ], true);
                }),

            Textarea::make('notes')
                ->label(__('applications::filament.fields.transition_notes'))
                ->rows(2),
        ];
    }
}
```

- [ ] **Step 4: Adicionar ao `ApplicationInfolist.php`**

- Adicionar `use He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions\MoveStageAction;` (ordem alfabética dos `use`).
- **Manter** o import e o uso de `StateTransitionAction`.
- No `Actions::make([...])`, inserir `MoveStageAction::make(),` logo após `StateTransitionAction::make(),`.

- [ ] **Step 5: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (2 passed).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php \
        app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/ApplicationInfolist.php \
        app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php
git -c commit.gpgsign=false commit -m "feat(panel-organization): add MoveStageAction alongside StateTransitionAction"
```

---

## Task 3: Avaliação opcional via toggle

**Contexto:** A `StateTransitionAction` sempre roda `StoreEvaluationAction` (obrigatório) — e continua assim, intacta. A nova `MoveStageAction` deve ter avaliação **opcional**: um `Toggle` `with_evaluation` (default `false`) que controla a visibilidade dos campos do `EvaluationForm`. Como `EvaluationForm::make()` marca `overall_rating` como `->required()`, os campos ficam dentro de um `Section` visível só com o toggle ligado (Filament não valida campos ocultos). Se ligado, roda `StoreEvaluationAction` com o mesmo payload da `StateTransitionAction` (ver `StateTransitionAction::processAction()`).

**Comportamento esperado (BDD):**

- **Dado** o toggle desligado **Quando** confirma **Então** transição ocorre **E** nenhum `Evaluation` criado.
- **Dado** o toggle ligado e campos preenchidos **Quando** confirma **Então** transição ocorre **E** um `Evaluation` é criado.

**Files:**

- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php`
- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`

- [ ] **Step 1: Escrever os testes que falham**

Append em `MoveStageActionTest.php`:

```php
it('does not create an evaluation when the toggle is off', function (): void {
    $targetStage = Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 999,
        'active' => true,
    ]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent(true),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => false,
            ],
        )
        ->assertHasNoActionErrors();

    expect(\He4rt\Feedback\Models\Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('creates an evaluation when the toggle is on', function (): void {
    $targetStage = Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 999,
        'active' => true,
    ]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent(true),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => true,
                'team_id' => $this->team->id,
                'application_id' => $this->application->id,
                'evaluator_id' => $this->admin->id,
                'overall_rating' => \He4rt\Feedback\Enums\EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => '8',
                    'communication' => '8',
                    'problem_solving' => '8',
                    'culture_fit' => '8',
                ],
                'comments' => 'ok',
                'recommendation' => 'hire',
                'strengths' => 's',
                'concerns' => 'c',
            ],
        )
        ->assertHasNoActionErrors();

    expect(\He4rt\Feedback\Models\Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(1);
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: "creates an evaluation when the toggle is on" FALHA (avaliação não implementada). "toggle is off" passa.

- [ ] **Step 3: Adicionar toggle + avaliação opcional**

Em `MoveStageAction.php`, adicionar imports:

```php
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use He4rt\Feedback\Actions\StoreEvaluationAction;
use He4rt\Feedback\DTOs\CriteriaScoresDTO;
use He4rt\Feedback\DTOs\EvaluationDTO;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\EvaluationForm;
```

No final do array de `buildSchema()`, após `Textarea::make('notes')`, adicionar:

```php
Toggle::make('with_evaluation')
    ->label(__('applications::filament.actions.move_stage.with_evaluation_label'))
    ->default(false)
    ->live(),

Section::make()
    ->hiddenLabel()
    ->visible(fn (Get $get): bool => (bool) $get('with_evaluation'))
    ->schema(EvaluationForm::make()),
```

Substituir o corpo de `processAction()` por:

```php
private function processAction(Application $record, array $data): void
{
    try {
        $transitionData = TransitionData::fromArray($data, auth()->id());
        $record->current_step->handle($transitionData);
    } catch (InvalidTransitionException|MissingTransitionDataException $e) {
        Notification::make()
            ->danger()
            ->title(__('applications::filament.actions.move_stage.notifications.error.title'))
            ->body($e->getMessage())
            ->send();

        return;
    }

    if (($data['with_evaluation'] ?? false) === true) {
        $criteria = $data['criteria_scores'];
        resolve(StoreEvaluationAction::class)->execute(new EvaluationDTO(
            teamId: $data['team_id'],
            applicationId: $record->getKey(),
            stageId: $record->current_stage_id,
            evaluatorId: $data['evaluator_id'],
            overallRating: $data['overall_rating'],
            recommendation: $data['recommendation'],
            strengths: $data['strengths'],
            concerns: $data['concerns'],
            notes: $data['notes'] ?? null,
            criteriaScores: CriteriaScoresDTO::make([
                'technical_skills' => $criteria['technical_skills'],
                'communication' => $criteria['communication'],
                'problem_solving' => $criteria['problem_solving'],
                'culture_fit' => $criteria['culture_fit'],
            ]),
        ));
    }

    Notification::make()
        ->success()
        ->title(__('applications::filament.actions.move_stage.notifications.moved.title'))
        ->send();
}
```

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (4 passed).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Actions/MoveStageAction.php \
        app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php
git -c commit.gpgsign=false commit -m "feat(panel-organization): make evaluation optional via toggle in MoveStageAction"
```

---

## Task 4: Adicionar `MoveStageAction` ao Kanban (ao lado da antiga)

**Contexto:** `KanbanStages.php` tem `->cardActions([ ViewCandidateAction, StateTransitionAction, RejectApplicationAction ])`. **Adicionar** `MoveStageAction::make()` (com a mesma `->visible()` por role usada na `StateTransitionAction`), **mantendo** a `StateTransitionAction`.

**Antes** (`KanbanStages.php`, dentro de `->cardActions([...])`):

```php
->cardActions([
    ViewCandidateAction::make()->model(Application::class),
    StateTransitionAction::make()
        ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin])),
    RejectApplicationAction::make()
        ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin])),
])
```

**Depois:**

```php
->cardActions([
    ViewCandidateAction::make()->model(Application::class),
    StateTransitionAction::make()
        ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin])),
    MoveStageAction::make()
        ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin])),
    RejectApplicationAction::make()
        ->visible(fn (): bool => (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin])),
])
```

(adicionar `use He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions\MoveStageAction;` em ordem alfabética; **manter** o import de `StateTransitionAction`)

**Files:**

- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/KanbanStages.php`
- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`

- [ ] **Step 1: Teste que falha — Kanban renderiza com ambas as ações**

Append em `MoveStageActionTest.php`:

```php
it('renders the kanban page with MoveStageAction available', function (): void {
    $requisition = $this->application->requisition;

    livewire(\He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\KanbanStages::class, [
        'tenant' => $this->team,
        'record' => $requisition->getKey(),
    ])->assertOk();

    $source = file_get_contents(base_path(
        'app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/KanbanStages.php'
    ));

    expect($source)->toContain('MoveStageAction::make()')
        ->and($source)->toContain('StateTransitionAction::make()');
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter="renders the kanban page with MoveStageAction"`
Expected: FAIL — `KanbanStages.php` ainda não referencia `MoveStageAction`.

- [ ] **Step 3: Adicionar a ação ao Kanban**

Editar `KanbanStages.php` conforme blocos Antes/Depois acima (adicionar import + `MoveStageAction::make()->visible(...)`; manter `StateTransitionAction`).

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (5 passed).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/JobRequisitions/Pages/Kanban/KanbanStages.php \
        app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php
git -c commit.gpgsign=false commit -m "feat(panel-organization): add MoveStageAction to Kanban card actions"
```

---

## Task 5: Remover a página de edição de candidatura

**Contexto:** O `ApplicationResource` expõe a rota `edit` (`getPages()`) com `canEdit()` liberado para `SuperAdmin/Admin`, e a `ApplicationsTable` tem um `EditAction`. Esse caminho (`EditApplication` + `ApplicationForm`) faz UPDATE cru de status/stage, burlando a máquina de estados. Remover a rota, o gate, o botão e os dois arquivos. `OwnerApplicationAccessTest` referencia `EditApplication`/`canEdit`/edit table action e precisa ser ajustado (NÃO mexer no que ele testa sobre `state-transition-action`, pois essa ação continua existindo).

**Comportamento esperado (BDD):**

- **Dado** `ApplicationResource::getPages()` **Então** não contém a chave `'edit'`.
- **Dado** a tabela de candidaturas **Então** não há ação `edit`; apenas `view`.
- **Compatibilidade:** mover etapa segue via `MoveStageAction`; `StateTransitionAction` segue existindo.

**Antes** (`ApplicationResource.php`):

```php
public static function canEdit(Model $record): bool
{
    return (bool) auth()->user()?->hasAnyRole([Roles::SuperAdmin, Roles::Admin]);
}

public static function canCreate(): bool
{
    return false;
}
// ...
public static function getPages(): array
{
    return [
        'index' => ListApplications::route('/'),
        'edit' => EditApplication::route('/{record}/edit'),
        'view' => ViewApplication::route('/{record}/view'),
    ];
}
```

**Depois:**

```php
public static function canCreate(): bool
{
    return false;
}
// ...
public static function getPages(): array
{
    return [
        'index' => ListApplications::route('/'),
        'view' => ViewApplication::route('/{record}/view'),
    ];
}
```

(remover também `use ...Pages\EditApplication;` e `use Illuminate\Database\Eloquent\Model;` se ficar órfão)

**Antes** (`ApplicationsTable.php`):

```php
use Filament\Actions\EditAction;
// ...
->recordActions([
    ViewAction::make(),
    EditAction::make()->visible(fn (Application $record): bool => ApplicationResource::canEdit($record)),
]);
```

**Depois:**

```php
// ...
->recordActions([
    ViewAction::make(),
]);
```

(remover `use Filament\Actions\EditAction;` e imports órfãos de `Application`/`ApplicationResource` se não usados em outro lugar do arquivo)

**Files:**

- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/ApplicationResource.php`
- Modify: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Tables/ApplicationsTable.php`
- Delete: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/EditApplication.php`
- Delete: `app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/ApplicationForm.php`
- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/OwnerApplicationAccessTest.php`

- [ ] **Step 1: Reescrever `OwnerApplicationAccessTest.php` (deve falhar)**

Substituir todo o conteúdo por:

```php
<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->owner = User::factory()->create();
    $this->owner->assignRole(Roles::Owner->value);
    actingAs($this->owner);

    $this->team = Team::factory()->create(['owner_id' => $this->owner->id]);
    $this->application = Application::factory()->create(['team_id' => $this->team->id]);

    JobPosting::factory()->for($this->application->requisition)->create();

    filament()->setTenant($this->team);
});

it('does not expose an edit route for applications', function (): void {
    expect(array_keys(ApplicationResource::getPages()))->not->toContain('edit');
});

it('does not show an edit button in the applications table', function (): void {
    livewire(ListApplications::class, ['tenant' => $this->team])
        ->assertOk()
        ->assertTableActionDoesNotExist('edit', $this->application);
});
```

- [ ] **Step 2: Rodar e confirmar falha**

Run: `php artisan test --compact --filter=OwnerApplicationAccessTest`
Expected: FAIL — rota `edit` ainda existe; `EditAction` ainda na tabela.

- [ ] **Step 3: Remover rota/gate, botão e arquivos**

- Editar `ApplicationResource.php` conforme Antes/Depois.
- Editar `ApplicationsTable.php` conforme Antes/Depois.
- Apagar:

```bash
git rm app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Pages/EditApplication.php \
       app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Schemas/ApplicationForm.php
```

- [ ] **Step 4: Rodar e confirmar que passa**

Run: `php artisan test --compact --filter=OwnerApplicationAccessTest`
Expected: PASS (2 passed).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/ApplicationResource.php \
        app-modules/panel-organization/src/Filament/Resources/Recruitment/Applications/Tables/ApplicationsTable.php \
        app-modules/panel-organization/tests/Feature/Filament/Application/OwnerApplicationAccessTest.php
git -c commit.gpgsign=false commit -m "refactor(panel-organization): remove application edit page (state-machine bypass)"
```

---

## Task 6: Edge-cases — transição inválida, visibilidade, não-admin

**Contexto:** Cobrir os edge-cases da spec ainda não exercitados: transição ilegal (rollback), visibilidade do `to_stage_id` (regressão do bug enum/string), e ação escondida para não-admin.

**Comportamento esperado (BDD):**

- **Dado** `InProgress` **Quando** admin tenta `to_status=Hired` **Então** status não muda e nenhum `ApplicationStageHistory` é criado.
- **Dado** `to_status=InProgress` selecionado **Então** o campo `to_stage_id` está visível.
- **Dado** usuário `Owner` **Então** `move-stage-action` está escondida.

**Files:**

- Test: `app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php`

- [ ] **Step 1: Escrever os testes**

Append em `MoveStageActionTest.php`:

```php
it('rolls back and does not create history on an illegal target status', function (): void {
    $originalStatus = $this->application->status;

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent(true),
            data: ['to_status' => ApplicationStatusEnum::Hired->value],
        );

    expect($this->application->fresh()->status)->toBe($originalStatus)
        ->and(ApplicationStageHistory::query()
            ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('shows the target stage select when status is InProgress', function (): void {
    Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 999,
        'active' => true,
    ]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('move-stage-action')->schemaComponent(true))
        ->setActionData(['to_status' => ApplicationStatusEnum::InProgress->value])
        ->assertActionFormFieldIsVisible(
            TestAction::make('move-stage-action')->schemaComponent(true),
            'to_stage_id',
        );
});

it('hides the move-stage action for non-admin users', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(Roles::Owner->value);
    actingAs($owner);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionHidden(TestAction::make('move-stage-action')->schemaComponent(true));
});
```

> Nota: se o método exato `assertActionFormFieldIsVisible` não existir nesta versão do plugin Filament/Pest, descobrir o equivalente correto inspecionando `vendor/filament` ou outros testes do repo que asseguram visibilidade de campo de formulário dentro de uma action montada, e usar o método correto — o teste DEVE falhar com a comparação enum/string quebrada e passar com a normalizada. Não enfraquecer a asserção.

- [ ] **Step 2: Rodar e confirmar que passam**

Run: `php artisan test --compact --filter=MoveStageActionTest`
Expected: PASS (8 passed). A transição ilegal é barrada por `current_step->handle()` (capturada em `processAction`); a visibilidade do campo já vem da comparação normalizada (Task 2 Step 3); a visibilidade da ação por role vem da `Section` Quick Actions.

- [ ] **Step 3: Commit**

```bash
git add app-modules/panel-organization/tests/Feature/Filament/Application/MoveStageActionTest.php
git -c commit.gpgsign=false commit -m "test(panel-organization): cover MoveStageAction edge cases"
```

---

## Task 7: Verificação final

**Contexto:** Garantir que nada quebrou e o estilo está conforme o projeto.

**Files:** nenhum (verificação).

- [ ] **Step 1: Suíte dos módulos afetados**

Run: `php artisan test --compact app-modules/panel-organization app-modules/applications`
Expected: PASS (sem falhas).

- [ ] **Step 2: Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: sem pendências.

- [ ] **Step 3: Confirmar ausência de referências órfãs ao edit page**

Run: `grep -rn "EditApplication\|ApplicationForm::configure" app-modules --include="*.php"`
Expected: nenhuma saída. (Observação: `StateTransitionAction` DEVE continuar referenciada — não é órfã nesta abordagem.)

- [ ] **Step 4: Commit final (se Pint alterou algo)**

```bash
git add -A
git -c commit.gpgsign=false commit -m "style: pint after move-stage feature" || echo "nada a commitar"
```

---

## Self-Review

- **Cobertura da spec (Rev. 2):** §3 coexistência → Tasks 2/4 (adiciona ao lado, não remove); §4 máquina de estados + nota de visibilidade → Tasks 2/6; §5 wireframe c/ toggle → Tasks 2/3; §6 mudanças por arquivo → Tasks 1–5; §7 edge-cases (incl. visibilidade + coexistência) → Tasks 2/6; remoção edit page → Task 5; i18n → Task 1. `StateTransitionAction` intacta → garantido (nenhuma task a modifica/remove; Tasks 2/4 mantêm import e uso). Sem lacunas.
- **Placeholders:** nenhum "TBD/TODO"; todo passo de código tem o código completo. Única flexibilidade explícita e justificada: nome do assert de visibilidade na Task 6 (com fallback de descoberta documentado, sem enfraquecer a asserção).
- **Consistência de tipos:** `move-stage-action`/`MoveStageAction` consistentes (Tasks 2/4/6); `state-transition-action`/`StateTransitionAction` referenciadas como existentes e mantidas; `TransitionData::fromArray`/`current_step->handle()` conforme assinaturas reais; `EvaluationDTO`/`CriteriaScoresDTO`/`StoreEvaluationAction` espelhados da `StateTransitionAction`; comparação de visibilidade normalizada (`BackedEnum` import) consistente Task 2 → Task 6.
