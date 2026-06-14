<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->recruiter = Recruiter::factory()->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    $this->team = $this->recruiter->team;

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

function makeRequisitionWithTitle(string $teamId, RequisitionStatusEnum $status, string $title): JobRequisition
{
    $requisition = JobRequisition::factory()->create([
        'team_id' => $teamId,
        'status' => $status,
    ]);

    JobPosting::factory()->for($requisition, 'jobRequisition')->create(['title' => $title]);

    return $requisition;
}

it('lists a published requisition as an option in the requisition filter', function (): void {
    makeRequisitionWithTitle($this->team->id, RequisitionStatusEnum::Published, 'PUBLISHED_VACANCY_OPTION');

    livewire(ListApplications::class)
        ->assertSuccessful()
        ->assertSee('PUBLISHED_VACANCY_OPTION');
});

it('does not list non-published requisitions as options in the requisition filter', function (RequisitionStatusEnum $status, string $title): void {
    makeRequisitionWithTitle($this->team->id, RequisitionStatusEnum::Published, 'PUBLISHED_VACANCY_OPTION');
    makeRequisitionWithTitle($this->team->id, $status, $title);

    livewire(ListApplications::class)
        ->assertSuccessful()
        ->assertSee('PUBLISHED_VACANCY_OPTION')
        ->assertDontSee($title);
})->with([
    'draft' => [RequisitionStatusEnum::Draft, 'DRAFT_VACANCY_OPTION'],
    'pending approval' => [RequisitionStatusEnum::PendingApproval, 'PENDING_VACANCY_OPTION'],
    'approved' => [RequisitionStatusEnum::Approved, 'APPROVED_VACANCY_OPTION'],
    'on hold' => [RequisitionStatusEnum::OnHold, 'ONHOLD_VACANCY_OPTION'],
    'closed' => [RequisitionStatusEnum::Closed, 'CLOSED_VACANCY_OPTION'],
    'cancelled' => [RequisitionStatusEnum::Cancelled, 'CANCELLED_VACANCY_OPTION'],
]);
