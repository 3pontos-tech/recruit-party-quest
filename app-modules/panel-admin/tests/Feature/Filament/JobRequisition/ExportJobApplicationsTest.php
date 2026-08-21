<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Applications\Filament\Actions\ExportJobApplicationsAction;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Bus::fake();
    Storage::fake(config('filament.default_filesystem_disk'));

    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());

    $this->team = Team::factory()->create();
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->department = Department::factory()
        ->for($this->team, 'team')
        ->state(['head_user_id' => $this->recruiter->user_id])
        ->create();

    $this->requisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create();
});

it('exports the applications of a requisition from the admin table', function (): void {
    $otherRequisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create();

    Application::factory()->count(2)->for($this->team)->for($this->requisition, 'requisition')->create();
    Application::factory()->count(3)->for($this->team)->for($otherRequisition, 'requisition')->create();

    livewire(ListJobRequisitions::class)
        ->callAction(TestAction::make(ExportJobApplicationsAction::class)->table($this->requisition))
        ->assertHasNoActionErrors();

    expect(Export::query()->latest('id')->first())->total_rows->toBe(2);
});

it('shows the export action on the admin edit page', function (): void {
    livewire(EditJobRequisition::class, ['record' => $this->requisition->getKey()])
        ->assertActionVisible(TestAction::make(ExportJobApplicationsAction::class));
});
