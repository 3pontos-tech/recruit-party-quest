<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Livewire\JobGenerationOverlay;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    filament()->setTenant($this->team);
});

it('renders in idle state by default', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->assertOk()
        ->assertSet('state', 'idle')
        ->assertSet('jobRequisitionId', null);
});

it('transitions to processing state when onProcessing is called', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onProcessing')
        ->assertSet('state', 'processing');
});

it('transitions to success state and captures the job id when onSuccess is called', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onSuccess', [
            'job_requisition_id' => 'job-uuid-123',
            'status' => 'success',
            'error_message' => null,
        ])
        ->assertSet('state', 'success')
        ->assertSet('jobRequisitionId', 'job-uuid-123')
        ->assertDispatched('redirect-after-delay');
});

it('transitions to error state when onError is called', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onError')
        ->assertSet('state', 'error');
});

it('resets to idle and clears the job id when closeOverlay is called', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onSuccess', [
            'job_requisition_id' => 'job-uuid-123',
            'status' => 'success',
            'error_message' => null,
        ])
        ->call('closeOverlay')
        ->assertSet('state', 'idle')
        ->assertSet('jobRequisitionId', null);
});

it('shows a warning notification and transitions to error on timeout', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onTimeout')
        ->assertSet('state', 'error')
        ->assertNotified();
});

it('redirects to the edit page when redirectToEdit is called with a valid job id', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('onSuccess', [
            'job_requisition_id' => 'job-uuid-123',
            'status' => 'success',
            'error_message' => null,
        ])
        ->call('redirectToEdit')
        ->assertRedirect();
});

it('does not redirect when redirectToEdit is called without a job id', function (): void {
    Livewire::test(JobGenerationOverlay::class)
        ->call('redirectToEdit')
        ->assertSet('state', 'idle')
        ->assertSet('jobRequisitionId', null);
});
