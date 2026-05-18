<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
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

    // StageFactory usa display_order aleatório (1..10) e o ApplicationFactory
    // aponta current_stage_id para um stage qualquer — então is_last_stage seria
    // não-determinístico, escondendo a MoveStageAction de forma intermitente.
    // Garantimos um stage sempre à frente para is_last_stage=false determinístico.
    Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 9999,
        'active' => true,
    ]);

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
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
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

it('keeps the legacy StateTransitionAction working alongside the new MoveStageAction', function (): void {
    // Behavioural coexistence proof: the new move-stage-action is exercised by the
    // other tests in this file; here we drive the LEGACY state-transition-action
    // through the same Quick Actions container and assert its real effects
    // (stage moved + its mandatory evaluation recorded). If it had been removed
    // or broken when adding the new action, this fails.
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
            TestAction::make('state-transition-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'notes' => 'Legacy action still works.',
                'team_id' => $this->team->id,
                'application_id' => $this->application->id,
                'evaluator_id' => $this->admin->id,
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => '7',
                    'communication' => '7',
                    'problem_solving' => '7',
                    'culture_fit' => '7',
                ],
                'comments' => 'ok',
                'recommendation' => 'hire',
                'strengths' => 's',
                'concerns' => 'c',
            ],
        )
        ->assertHasNoActionErrors();

    expect($this->application->fresh()->current_stage_id)->toBe($targetStage->id)
        ->and(Evaluation::query()
            ->where('application_id', $this->application->id)->count())->toBe(1);
});

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
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => false,
            ],
        )
        ->assertHasNoActionErrors();

    expect(Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('rolls back and does not create stage history on an illegal target status', function (): void {
    $originalStatus = $this->application->status;

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: ['to_status' => ApplicationStatusEnum::Hired->value],
        );

    expect($this->application->fresh()->status)->toBe($originalStatus)
        ->and(ApplicationStageHistory::query()
            ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('does not expose the move-stage action to non-admin users', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(Roles::Owner->value);
    actingAs($owner);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionDoesNotExist(TestAction::make('move-stage-action')->schemaComponent('quick-actions'));
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
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => true,
                'team_id' => $this->team->id,
                'application_id' => $this->application->id,
                'evaluator_id' => $this->admin->id,
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
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

    expect(Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(1);
});

// #16 — "Mover etapa" não deve oferecer "Retirar candidatura" (Withdrawn):
// retirar tem fluxo próprio e não é uma mudança de etapa.
it('does not offer the Withdrawn status as a move-stage option', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('move-stage-action')->schemaComponent('quick-actions'))
        ->assertSchemaComponentExists(
            'to_status',
            checkComponentUsing: function ($component): bool {
                $options = $component->getOptions();

                return array_key_exists(ApplicationStatusEnum::InProgress->value, $options)
                    && ! array_key_exists(ApplicationStatusEnum::Withdrawn->value, $options);
            },
        );
});

// #8 — guarda de regressão da visibilidade dependente de status do campo
// `to_stage_id`: visível quando `to_status = InProgress`, oculto para os demais
// (ex.: Withdrawn). NÃO guarda um "bug enum/string" — experimento controlado
// provou que $get('to_status') retorna instância de enum aqui, então o campo
// já se comportava corretamente; este teste protege contra futuras regressões
// na lógica de visibilidade (remoção do visible(), troca de status, etc.).
it('shows the target stage field for InProgress and hides it otherwise', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('move-stage-action')->schemaComponent('quick-actions'))
        ->setActionData(['to_status' => ApplicationStatusEnum::InProgress->value])
        ->assertSchemaComponentExists(
            'to_stage_id',
            checkComponentUsing: fn ($component): bool => $component->isVisible(),
        )
        ->setActionData(['to_status' => ApplicationStatusEnum::Withdrawn->value])
        ->assertSchemaComponentExists(
            'to_stage_id',
            checkComponentUsing: fn ($component): bool => ! $component->isVisible(),
        );
});

// #10 — candidato já na última etapa do pipeline: a ação some.
it('hides the move-stage action when the application is on the last stage', function (): void {
    [$application, $stage] = Application::factory()
        ->withStatus(ApplicationStatusEnum::InProgress)
        ->withIsolatedStages()
        ->createWithStage(['team_id' => $this->team->id]);

    $application->update(['current_stage_id' => $stage->id]);
    JobPosting::factory()->for($application->requisition)->create();

    expect($application->fresh()->is_last_stage)->toBeTrue();

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $application->getKey(),
    ])
        ->assertOk()
        ->assertActionDoesNotExist(TestAction::make('move-stage-action')->schemaComponent('quick-actions'));
});

// #11 — status terminal (Hired): a ação existe porém desabilitada (canChange()=false).
it('disables the move-stage action for a terminal status', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::Hired)
        ->withNoStages()
        ->create(['team_id' => $this->team->id]);
    JobPosting::factory()->for($application->requisition)->create();

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $application->getKey(),
    ])
        ->assertOk()
        ->assertActionDisabled(TestAction::make('move-stage-action')->schemaComponent('quick-actions'));
});

// #12 — toggle de avaliação ligado MAS transição inválida: nada é gravado
// (o `return` no catch acontece antes do bloco de avaliação).
it('does not record an evaluation when the transition fails even with the toggle on', function (): void {
    $originalStatus = $this->application->status;

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::Hired->value,
                'with_evaluation' => true,
                'team_id' => $this->team->id,
                'application_id' => $this->application->id,
                'evaluator_id' => $this->admin->id,
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => '8',
                    'communication' => '8',
                    'problem_solving' => '8',
                    'culture_fit' => '8',
                ],
                'comments' => 'x',
                'recommendation' => 'x',
                'strengths' => 'x',
                'concerns' => 'x',
            ],
        );

    expect($this->application->fresh()->status)->toBe($originalStatus)
        ->and(ApplicationStageHistory::query()
            ->where('application_id', $this->application->id)->count())->toBe(0)
        ->and(Evaluation::query()
            ->where('application_id', $this->application->id)->count())->toBe(0);
});

// #15 — a ação não é só "InProgress→InProgress": a partir de New ela usa a
// NewTransition (auto-avança 1 stage), e de InReview usa a InReviewTransition.
it('moves a New application to InReview auto-advancing the stage', function (): void {
    [$application, $stage] = Application::factory()
        ->withStatus(ApplicationStatusEnum::New)
        ->withIsolatedStages()
        ->createWithStage(['team_id' => $this->team->id]);
    JobPosting::factory()->for($application->requisition)->create();

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: ['to_status' => ApplicationStatusEnum::InReview->value],
        )
        ->assertHasNoActionErrors();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->fresh()->current_stage_id)->toBe($stage->id);
});

it('moves an InReview application to InProgress at the chosen stage', function (): void {
    [$application, $stage] = Application::factory()
        ->withStatus(ApplicationStatusEnum::InReview)
        ->withIsolatedStages()
        ->createWithStage(['team_id' => $this->team->id]);
    JobPosting::factory()->for($application->requisition)->create();

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $application->getKey(),
    ])
        ->callAction(
            TestAction::make('move-stage-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $stage->id,
            ],
        )
        ->assertHasNoActionErrors();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InProgress)
        ->and($application->fresh()->current_stage_id)->toBe($stage->id);
});
