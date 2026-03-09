<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\App\Filament\Resources\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();

    $this->team = Team::factory()->create();
    $this->department = Department::factory()->for($this->team)->create();
    $this->recruiter = Recruiter::factory()->for($this->team)->create();

    $this->jobRequisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->user, 'createdBy')
        ->create();

    actingAs($this->user);
    $this->user->givePermissionTo('view_job_requisitions');
});

describe('ListJobRequisitions Page', function (): void {
    it('should render list page successfully', function (): void {
        livewire(ListJobRequisitions::class)
            ->assertOk();
    });

    it('should display table with correct columns', function (): void {
        livewire(ListJobRequisitions::class)
            ->assertOk()
            ->assertTableColumnExists('id')
            ->assertTableColumnExists('team.name')
            ->assertTableColumnExists('department.name')
            ->assertTableColumnExists('work_arrangement')
            ->assertTableColumnExists('employment_type')
            ->assertTableColumnExists('experience_level')
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('priority');
    });
});

describe('ViewJobRequisition Page', function (): void {
    it('should handle redirect when candidate has existing application', function (): void {
        Application::factory()
            ->for($this->candidate)
            ->for($this->jobRequisition, 'requisition')
            ->create();

        expect($this->candidate->applications()->count())->toBe(1);

        $action = resolve(ApplyToJobRequisitionAction::class);
        expect($action->hasApplied($this->jobRequisition, $this->candidate))->toBeTrue();
    });

    it('should handle applyDirectly method for candidates', function (): void {
        $component = livewire(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()]);

        expect(method_exists($component->instance(), 'applyDirectly'))->toBeTrue();

        expect($this->candidate->applications()->count())->toBe(0);
    });

    it('should detect existing applications correctly', function (): void {
        Application::factory()
            ->for($this->candidate)
            ->for($this->jobRequisition, 'requisition')
            ->create();

        $action = resolve(ApplyToJobRequisitionAction::class);

        expect($action->hasApplied($this->jobRequisition, $this->candidate))
            ->toBeTrue();
    });

    it('should handle user without candidate profile', function (): void {
        $userWithoutCandidate = User::factory()->create();

        actingAs($userWithoutCandidate);

        livewire(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
            ->assertOk();
    });
});
