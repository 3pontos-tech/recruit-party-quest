<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user);
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    $this->candidate->user->givePermissionTo('view_applications');
});

it('renders pipeline progress sidebar for active application', function (): void {
    $this->application->update(['status' => ApplicationStatusEnum::InReview]);

    $stage = Stage::factory()->create([
        'job_requisition_id' => $this->application->requisition_id,
        'hidden' => false,
        'active' => true,
        'display_order' => 1,
    ]);
    $this->application->update(['current_stage_id' => $stage->id]);

    $this->application->requisition->stages()->whereNot('id', $stage->id)->update(['hidden' => true]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee('Selection Process Stages')
        ->assertSee('Overall Progress')
        ->assertSee($stage->name)
        ->assertSee('Current');
});

it('shows stage counter badge with correct position', function (): void {
    $this->application->update(['status' => ApplicationStatusEnum::InReview]);

    $stage1 = Stage::factory()->create(['job_requisition_id' => $this->application->requisition_id, 'stage_type' => StageTypeEnum::Screening, 'hidden' => false, 'active' => true, 'display_order' => 1]);
    $stage2 = Stage::factory()->create(['job_requisition_id' => $this->application->requisition_id, 'stage_type' => StageTypeEnum::Assessment, 'hidden' => false, 'active' => true, 'display_order' => 2]);
    $stage3 = Stage::factory()->create(['job_requisition_id' => $this->application->requisition_id, 'stage_type' => StageTypeEnum::Interview, 'hidden' => false, 'active' => true, 'display_order' => 3]);

    $this->application->update(['current_stage_id' => $stage2->id]);

    $this->application->requisition->stages()
        ->whereNotIn('id', [$stage1->id, $stage2->id, $stage3->id])
        ->update(['hidden' => true]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee('2 / 3');
});

it('shows submission date and last updated in footer', function (): void {
    $this->application->update(['status' => ApplicationStatusEnum::InReview]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee('Application submitted')
        ->assertSee('Last updated')
        ->assertSee($this->application->created_at->format('M j, Y'));
});

it('shows neutral title and rejection details for rejected application', function (): void {
    $application = Application::factory()
        ->rejected()
        ->for($this->candidate, 'candidate')
        ->create([
            // Pin a non-knockout category: ScreeningKnockout renders a neutral message
            // and hides the reason, so relying on ->rejected()'s random category is flaky.
            'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('Application Not Progressed')
        ->assertDontSee('Selection Process Stages')
        ->assertSee('Rejection Reason')
        ->assertSee($application->rejection_reason_category->getLabel());
});

it('shows rejection reason category when present', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
            'rejection_reason_details' => null,
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('Rejection Reason')
        ->assertSee(RejectionReasonCategoryEnum::Qualifications->getLabel());
});

it('shows rejection reason details when present', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_details' => 'Did not meet the technical requirements.',
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('Did not meet the technical requirements.');
});

it('does not show details section when rejection details are absent', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_details' => null,
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertDontSee('Feedback');
});

it('shows neutral message and hides reason for screening knockout rejection', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout,
            'rejection_reason_details' => 'Automatically rejected: did not meet the screening criteria.',
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('your application will not proceed to the next stages')
        ->assertDontSee('Rejection Reason')
        ->assertDontSee(RejectionReasonCategoryEnum::ScreeningKnockout->getLabel())
        ->assertDontSee('Automatically rejected: did not meet the screening criteria.');
});

it('does not show neutral screening message for non-knockout rejection', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
            'rejection_reason_details' => 'Did not meet the technical requirements.',
        ]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('Rejection Reason')
        ->assertSee('Did not meet the technical requirements.')
        ->assertDontSee('your application will not proceed to the next stages');
});

it('shows a closure card and hides the timeline for a withdrawn application', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::Withdrawn)
        ->for($this->candidate, 'candidate')
        ->create();

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee(__('panel-organization::view.pipeline.withdrawn_title'))
        ->assertSee(__('panel-organization::view.pipeline.withdrawn_message'))
        ->assertDontSee('Selection Process Stages');
});

it('shows a closure card and hides the timeline for a declined offer', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::OfferDeclined)
        ->for($this->candidate, 'candidate')
        ->create();

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee(__('panel-organization::view.pipeline.declined_title'))
        ->assertSee(__('panel-organization::view.pipeline.declined_message'))
        ->assertDontSee('Selection Process Stages');
});

it('allows candidate to view their own application when job is not published', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();

    $application->requisition->update(['status' => RequisitionStatusEnum::Closed]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk();
});

it('redirects non-applicant when job is not published', function (): void {
    $otherCandidate = Candidate::factory()->create();
    $application = Application::factory()
        ->for($otherCandidate, 'candidate')
        ->create();

    $application->requisition->update(['status' => RequisitionStatusEnum::Closed]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertRedirect(JobRequisitionResource::getUrl('index'));
});

it('allows anyone with permission to view application when job is published', function (): void {
    $application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();

    $application->requisition->update(['status' => RequisitionStatusEnum::Published]);

    livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk();
});
