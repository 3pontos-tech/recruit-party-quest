<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

beforeEach(function (): void {
    // ApplicationFactory::configure() posiciona o current_stage na primeira etapa
    // (criada pelo JobRequisitionObserver) quando requisition_id está setado.
    $this->requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $this->application = Application::factory()
        ->withStatus(ApplicationStatusEnum::New)
        ->create([
            'requisition_id' => $this->requisition->id,
            'team_id' => $this->requisition->team_id,
        ]);
});

it('rejects a New application when the flag is still on', function (): void {
    new RejectScreeningKnockoutJob($this->application)->handle();

    $this->application->refresh();

    expect($this->application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($this->application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout)
        ->and($this->application->rejected_by)->toBeNull();
});

it('is a no-op when status is no longer New', function (): void {
    $this->application->update(['status' => ApplicationStatusEnum::InReview]);

    new RejectScreeningKnockoutJob($this->application)->handle();

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::InReview);
});

it('is a no-op when the requisition flag has been turned off', function (): void {
    $this->requisition->update(['auto_screening_transition' => false]);

    new RejectScreeningKnockoutJob($this->application)->handle();

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('is idempotent: a second job execution does not change anything', function (): void {
    new RejectScreeningKnockoutJob($this->application)->handle();
    $rejectedAt = $this->application->fresh()->rejected_at;

    new RejectScreeningKnockoutJob($this->application->fresh())->handle();

    $this->application->refresh();

    expect($this->application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($this->application->rejected_at?->equalTo($rejectedAt))->toBeTrue();
});
