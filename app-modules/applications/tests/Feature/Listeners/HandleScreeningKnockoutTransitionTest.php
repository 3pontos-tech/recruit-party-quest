<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Events\ScreeningEvaluated;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    // ApplicationFactory::configure() posiciona o current_stage na primeira etapa
    // (criada pelo JobRequisitionObserver) — sem fabricar stages manualmente.
    $this->requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $this->application = Application::factory()
        ->withStatus(ApplicationStatusEnum::New)
        ->create([
            'requisition_id' => $this->requisition->id,
            'team_id' => $this->requisition->team_id,
        ]);
});

it('does nothing when the flag is off', function (): void {
    $this->requisition->update(['auto_screening_transition' => false]);

    event(new ScreeningEvaluated($this->application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('queues a delayed rejection job when the flag is on and a knockout failed', function (): void {
    Queue::fake();
    $this->freezeTime();

    event(new ScreeningEvaluated($this->application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::New);

    Queue::assertPushed(
        RejectScreeningKnockoutJob::class,
        fn (RejectScreeningKnockoutJob $job): bool => $job->application->is($this->application)
            && $job->delay instanceof DateTimeInterface
            && $job->delay->getTimestamp() === now()->addDay()->getTimestamp()
    );
});

it('advances the candidate when the flag is on, has knockout questions and none failed', function (): void {
    $secondStageId = $this->requisition->stages()->orderBy('display_order')->skip(1)->first()->id;

    event(new ScreeningEvaluated($this->application, anyKnockoutFailed: false, hadKnockoutCriteria: true));

    $this->application->refresh();

    expect($this->application->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($this->application->current_stage_id)->toBe($secondStageId);
});

it('does nothing when there were no knockout questions', function (): void {
    event(new ScreeningEvaluated($this->application, anyKnockoutFailed: false, hadKnockoutCriteria: false));

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('ignores applications not in New status', function (): void {
    $this->application->update(['status' => ApplicationStatusEnum::InProgress]);

    event(new ScreeningEvaluated($this->application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($this->application->fresh()->status)->toBe(ApplicationStatusEnum::InProgress);
});
