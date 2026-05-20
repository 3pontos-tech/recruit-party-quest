<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Events\ScreeningEvaluated;
use Illuminate\Support\Facades\Queue;

// JobRequisitionObserver::created() já cria 8 stages ordenados (display_order 1..8).
// Não fabricar stages manualmente — usar os do observer evita display_order colidente.
function newApplicationFor(JobRequisition $req): Application
{
    $first = $req->stages()->orderBy('display_order')->first();

    return Application::factory()->create([
        'requisition_id' => $req->id,
        'team_id' => $req->team_id,
        'status' => ApplicationStatusEnum::New,
        'current_stage_id' => $first->id,
    ]);
}

it('does nothing when the flag is off', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => false]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('queues a delayed rejection job when the flag is on and a knockout failed', function (): void {
    Queue::fake();

    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);

    Queue::assertPushed(
        RejectScreeningKnockoutJob::class,
        fn (RejectScreeningKnockoutJob $job): bool => $job->application->is($application)
            && $job->delay instanceof DateTimeInterface
            && $job->delay->getTimestamp() >= now()->addDay()->subMinute()->getTimestamp()
            && $job->delay->getTimestamp() <= now()->addDay()->addMinute()->getTimestamp()
    );
});

it('advances the candidate when the flag is on, has knockout questions and none failed', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);
    $secondStageId = $req->stages()->orderBy('display_order')->skip(1)->first()->id;

    event(new ScreeningEvaluated($application, anyKnockoutFailed: false, hadKnockoutCriteria: true));

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::InReview)
        ->and($application->current_stage_id)->toBe($secondStageId);
});

it('does nothing when there were no knockout questions', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: false, hadKnockoutCriteria: false));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('ignores applications not in New status', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationFor($req);
    $application->update(['status' => ApplicationStatusEnum::InProgress]);

    event(new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true));

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InProgress);
});
