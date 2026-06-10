<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Actions\RejectApplicationAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::Admin->value);
    actingAs($this->admin);

    $this->team = Team::factory()->create(['owner_id' => $this->admin->id]);
    $this->application = Application::factory()
        ->withStatus(ApplicationStatusEnum::InReview)
        ->create(['team_id' => $this->team->id]);

    JobPosting::factory()->for($this->application->requisition)->create();
    filament()->setTenant($this->team);
});

it('should render', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $this->application->getKey(),
    ])->assertOk();
});

it('should be able to reject an application', function (): void {
    Event::fake([ApplicationStatusChanged::class]);

    $sut = new RejectApplicationAction();
    $sut->execute(
        applicationId: $this->application->getKey(),
        reason: RejectionReasonCategoryEnum::Availability,
        details: 'n sei'
    );

    assertDatabaseHas(Application::class, [
        'id' => $this->application->getKey(),
        'status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_details' => 'n sei',
        'rejection_reason_category' => RejectionReasonCategoryEnum::Availability,
    ]);

    // Auditoria: a rejeição passa pela máquina de estados, gravando uma linha de
    // histórico como overlay (mesmo estágio) e atribuída ao sistema (moved_by null).
    $history = $this->application->stageHistory()->get();

    expect($history)->toHaveCount(1)
        ->and($history->first()->moved_by)->toBeNull()
        ->and($history->first()->from_stage_id)->toBe($this->application->current_stage_id)
        ->and($history->first()->to_stage_id)->toBe($this->application->current_stage_id);

    Event::assertDispatched(ApplicationStatusChanged::class);
});

it('rejects through the Filament action, persisting status and notifying', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('reject_application-action')->schemaComponent('quick-actions'),
            data: [
                'rejection_reason_category' => RejectionReasonCategoryEnum::Compensation->value,
                'rejection_reason_details' => 'budget acima do teto',
            ],
        )
        ->assertHasNoActionErrors()
        ->assertNotified();

    assertDatabaseHas(Application::class, [
        'id' => $this->application->getKey(),
        'status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::Compensation,
    ]);
});

it('requires a rejection reason category in the Filament action', function (): void {
    livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $this->application->getKey(),
    ])
        ->callAction(
            TestAction::make('reject_application-action')->schemaComponent('quick-actions'),
            data: [
                'rejection_reason_category' => null,
                'rejection_reason_details' => 'sem categoria',
            ],
        )
        ->assertHasActionErrors(['rejection_reason_category' => 'required']);

    expect($this->application->refresh()->status)->toBe(ApplicationStatusEnum::InReview);
});

it('refuses to reject an application that already has an offer extended', function (): void {
    $application = Application::factory()->withOffer()->createOne();

    expect(fn () => new RejectApplicationAction()->execute(
        applicationId: $application->getKey(),
        reason: RejectionReasonCategoryEnum::Other,
    ))->toThrow(InvalidTransitionException::class);

    expect($application->refresh()->status)->toBe(ApplicationStatusEnum::OfferExtended);
});

it('keeps the application on its current stage when rejected (overlay)', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::InReview)
        ->createOne();
    $stage = Stage::factory()->create(['job_requisition_id' => $application->requisition_id]);
    $application->update(['current_stage_id' => $stage->id]);

    new RejectApplicationAction()->execute(
        applicationId: $application->getKey(),
        reason: RejectionReasonCategoryEnum::Other,
    );

    $application->refresh();
    $history = $application->stageHistory()->first();

    expect($application->current_stage_id)->toBe($stage->id)
        ->and($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($history->from_stage_id)->toBe($stage->id)
        ->and($history->to_stage_id)->toBe($stage->id);
});
