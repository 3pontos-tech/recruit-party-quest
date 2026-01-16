<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Applications\ApplicationResource;
use He4rt\Admin\Filament\Resources\Applications\Pages\CreateApplication;
use He4rt\Admin\Filament\Resources\Applications\Pages\EditApplication;
use He4rt\Admin\Filament\Resources\Applications\Pages\ListApplications;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list applications', function (): void {
    $applications = Application::factory()->count(3)->create();

    livewire(ListApplications::class)
        ->assertCanSeeTableRecords($applications);
});

it('can render create application page', function (): void {
    $this->get(ApplicationResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit application page', function (): void {
    $application = Application::factory()->create();

    $this->get(ApplicationResource::getUrl('edit', ['record' => $application]))
        ->assertSuccessful();
});

it('can create an application', function (): void {
    $requisition = JobRequisition::factory()->create();
    $candidate = Candidate::factory()->create();

    livewire(CreateApplication::class)
        ->fillForm([
            'team_id' => $requisition->team_id,
            'requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'status' => ApplicationStatusEnum::New,
            'source' => CandidateSourceEnum::LinkedIn,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('applications', [
        'requisition_id' => $requisition->id,
        'candidate_id' => $candidate->id,
        'status' => ApplicationStatusEnum::New->value,
        'source' => CandidateSourceEnum::LinkedIn->value,
    ]);
});

it('can update an application', function (): void {
    $requisition = JobRequisition::factory()->create();
    $application = Application::factory()
        ->recycle($requisition)
        ->create();

    livewire(EditApplication::class, ['record' => $application->id])
        ->fillForm([
            'requisition_id' => $application->requisition_id,
            'status' => ApplicationStatusEnum::InReview,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => ApplicationStatusEnum::InReview->value,
    ]);
});

it('can filter applications by status', function (): void {
    $newApplication = Application::factory()->create(['status' => ApplicationStatusEnum::New]);
    $inReviewApplication = Application::factory()->create(['status' => ApplicationStatusEnum::InReview]);

    livewire(ListApplications::class)
        ->assertCanSeeTableRecords([$newApplication, $inReviewApplication])
        ->filterTable('status', ApplicationStatusEnum::New->value)
        ->assertCanSeeTableRecords([$newApplication])
        ->assertCanNotSeeTableRecords([$inReviewApplication]);
});
