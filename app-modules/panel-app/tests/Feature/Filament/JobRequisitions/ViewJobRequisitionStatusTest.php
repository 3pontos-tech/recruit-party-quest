<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Resources\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

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
    $candidate = candidateFor($user, ['is_onboarded' => true]);

    $application = Application::factory()
        ->for($candidate)
        ->for($requisition, 'requisition')
        ->create();

    actingAs($user);

    get(route('filament.app.resources.vagas.view', ['record' => $posting->slug]))
        ->assertRedirect(route('filament.app.resources.applications.view', ['record' => $application->getKey()]));
});

it('redirects to the jobs list with a warning when the job is unpublished between mount and applyDirectly', function (): void {
    $requisition = makeRequisition();
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $user = User::factory()->create();
    $candidate = candidateFor($user, ['is_onboarded' => true]);

    actingAs($user);

    $component = livewire(ViewJobRequisition::class, ['record' => $posting->slug]);

    $requisition->update(['status' => RequisitionStatusEnum::Closed]);

    $component->call('applyDirectly')
        ->assertRedirect(route('filament.app.resources.vagas.index'))
        ->assertSessionHas('filament.notifications');

    assertDatabaseCount(Application::class, 0);
});
