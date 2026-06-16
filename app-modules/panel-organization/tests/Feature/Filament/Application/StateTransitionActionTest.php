<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\ToggleButtons;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Organization\Filament\Forms\Components\ScoreMeter;
use He4rt\Organization\Filament\Forms\Components\StageTimeline;
use He4rt\Organization\Filament\Forms\Components\StatusHeroBand;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
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

it('records the evaluation when the toggle is on', function (): void {
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
                'with_evaluation' => true,
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
            ],
        )
        ->assertHasNoActionErrors()
        ->assertNotified(__('applications::filament.actions.change_status.notifications.updated.title'));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->fresh()->current_stage_id)->toBe($stage->id);
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
            TestAction::make('state-transition-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::InProgress->value,
                'to_stage_id' => $targetStage->id,
                'with_evaluation' => false,
            ],
        )
        ->assertHasNoActionErrors();

    expect($this->application->fresh()->current_stage_id)->toBe($targetStage->id)
        ->and(Evaluation::query()
            ->where('application_id', $this->application->id)->count())->toBe(0);
});

it('requires every criterion score when the evaluation toggle is on', function (): void {
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
                'with_evaluation' => true,
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

it('remains available on the last stage (gated by canChange, not stage position)', function (): void {
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
        ->assertActionVisible(TestAction::make('state-transition-action')->schemaComponent('quick-actions'));
});

it('exposes the offer and hiring statuses in the picker and excludes Rejected', function (): void {
    $application = Application::factory()
        ->withOffer()
        ->create(['team_id' => $this->team->id]);
    Stage::factory()->create([
        'job_requisition_id' => $application->requisition_id,
        'display_order' => 9999,
        'active' => true,
    ]);
    JobPosting::factory()->for($application->requisition)->create();

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->assertSchemaComponentExists('to_status', checkComponentUsing: function ($component): bool {
            $keys = array_keys($component->getOptions());

            return in_array(ApplicationStatusEnum::OfferAccepted->value, $keys, true)
                && in_array(ApplicationStatusEnum::OfferDeclined->value, $keys, true)
                && ! in_array(ApplicationStatusEnum::Rejected->value, $keys, true);
        });
});

it('reveals the offer fields only when extending an offer', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->setActionData(['to_status' => ApplicationStatusEnum::OfferExtended->value])
        ->assertSchemaComponentExists('offer_amount', checkComponentUsing: fn ($c): bool => $c->isVisible())
        ->assertSchemaComponentExists('offer_response_deadline', checkComponentUsing: fn ($c): bool => $c->isVisible())
        ->assertSchemaComponentExists('to_stage_id', checkComponentUsing: fn ($c): bool => ! $c->isVisible())
        ->setActionData(['to_status' => ApplicationStatusEnum::InProgress->value])
        ->assertSchemaComponentExists('offer_amount', checkComponentUsing: fn ($c): bool => ! $c->isVisible());
});

it('extends an offer (InProgress → OfferExtended) and advances to the offer stage', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('state-transition-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::OfferExtended->value,
                'offer_amount' => 9500,
                'offer_response_deadline' => now()->addDays(7)->toDateString(),
            ],
        )
        ->assertHasNoActionErrors();

    $fresh = $this->application->fresh()->load('currentStage');
    expect($fresh->status)->toBe(ApplicationStatusEnum::OfferExtended)
        ->and((float) $fresh->offer_amount)->toBe(9500.0)
        ->and($fresh->currentStage->stage_type)->toBe(StageTypeEnum::Offer);
});

it('keeps the offer fields null when amount and deadline are left blank', function (): void {
    $this->application->update(['offer_amount' => null, 'offer_response_deadline' => null]);

    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('state-transition-action')->schemaComponent('quick-actions'),
            data: [
                'to_status' => ApplicationStatusEnum::OfferExtended->value,
                'offer_amount' => '',
                'offer_response_deadline' => '',
            ],
        )
        ->assertHasNoActionErrors();

    $fresh = $this->application->fresh();
    expect($fresh->status)->toBe(ApplicationStatusEnum::OfferExtended)
        ->and($fresh->offer_amount)->toBeNull()
        ->and($fresh->offer_response_deadline)->toBeNull();
});

it('renders the status picker as a custom hero band', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->assertSchemaComponentExists(
            'to_status',
            checkComponentUsing: fn ($component): bool => $component instanceof StatusHeroBand,
        );
});

it('renders the target stage as a custom timeline field instead of a select', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->setActionData(['to_status' => ApplicationStatusEnum::InProgress->value])
        ->assertSchemaComponentExists(
            'to_stage_id',
            checkComponentUsing: fn ($component): bool => $component instanceof StageTimeline,
        );
});

it('renders the overall rating as colored toggle buttons instead of a select', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->setActionData(['with_evaluation' => true])
        ->assertSchemaComponentExists(
            'overall_rating',
            checkComponentUsing: fn ($component): bool => $component instanceof ToggleButtons,
        );
});

it('renders each evaluation criterion as a score-meter field', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->setActionData(['with_evaluation' => true])
        ->assertSchemaComponentExists(
            'criteria_scores.technical_skills',
            checkComponentUsing: fn ($component): bool => $component instanceof ScoreMeter,
        );
});

it('shows all four feedback note fields without hiding them in tabs', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => $this->team,
        'record' => $this->application->getKey(),
    ])
        ->mountAction(TestAction::make('state-transition-action')->schemaComponent('quick-actions'))
        ->setActionData(['with_evaluation' => true])
        ->assertSchemaComponentExists('strengths')
        ->assertSchemaComponentExists('concerns')
        ->assertSchemaComponentExists('recommendation')
        ->assertSchemaComponentExists('comments')
        ->assertSchemaComponentDoesNotExist('evaluation_feedback');
});
