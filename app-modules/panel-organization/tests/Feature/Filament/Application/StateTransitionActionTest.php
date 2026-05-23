<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
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

    // Garante um stage sempre à frente para is_last_stage=false determinístico
    // (StageFactory usa display_order aleatório, o que esconderia a ação de forma intermitente).
    Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'display_order' => 9999,
        'active' => true,
    ]);

    JobPosting::factory()->for($this->application->requisition)->create();

    filament()->setTenant($this->team);
});

it('shows the change-status action for admins', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionVisible(TestAction::make('state-transition-action')->schemaComponent('quick-actions'));
});

it('moves the candidate stage and records the mandatory evaluation', function (): void {
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
                'notes' => 'moved forward',
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => 5,
                    'communication' => 4,
                    'problem_solving' => 3,
                    'culture_fit' => 5,
                ],
                'comments' => 'ok',
                'recommendation' => 'hire',
                'strengths' => 's',
                'concerns' => 'c',
            ],
        )
        ->assertHasNoActionErrors();

    expect($this->application->fresh()->current_stage_id)->toBe($targetStage->id);

    $evaluation = Evaluation::query()
        ->where('application_id', $this->application->id)
        ->sole();

    // Asserção por chave: o Postgres jsonb não preserva a ordem ao reler,
    // e os ->toBe(int) também provam o tipo inteiro.
    expect($evaluation->criteria_scores['technical_skills'])->toBe(5)
        ->and($evaluation->criteria_scores['communication'])->toBe(4)
        ->and($evaluation->criteria_scores['problem_solving'])->toBe(3)
        ->and($evaluation->criteria_scores['culture_fit'])->toBe(5);
});

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
            TestAction::make('state-transition-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InReview->value,
                'notes' => 'starting review',
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => 4,
                    'communication' => 4,
                    'problem_solving' => 4,
                    'culture_fit' => 4,
                ],
                'comments' => 'ok',
                'recommendation' => 'hire',
                'strengths' => 's',
                'concerns' => 'c',
            ],
        )
        ->assertHasNoActionErrors();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->fresh()->current_stage_id)->toBe($stage->id);
});

it('requires every criterion score because the evaluation is mandatory', function (): void {
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
                'overall_rating' => EvaluationRatingEnum::cases()[0]->value,
                'criteria_scores' => [
                    'technical_skills' => 4,
                    // communication ausente de propósito
                    'problem_solving' => 4,
                    'culture_fit' => 4,
                ],
            ],
        )
        ->assertHasActionErrors(['criteria_scores.communication' => 'required']);

    expect(Evaluation::query()
        ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('shows the target stage field for InProgress and hides it for Withdrawn', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
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

it('does not expose the change-status action to non-admin users', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(Roles::Owner->value);
    actingAs($owner);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->assertOk()
        ->assertActionDoesNotExist(TestAction::make('state-transition-action')->schemaComponent('quick-actions'));
});

it('hides the change-status action when the application is on the last stage', function (): void {
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
        ->assertActionDoesNotExist(TestAction::make('state-transition-action')->schemaComponent('quick-actions'));
});
