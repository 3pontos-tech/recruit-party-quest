<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\NewTransition;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;

it('can transition from New to InReview', function (): void {
    $user = User::factory()->create();
    $requisition = JobRequisition::factory()->has(Stage::factory()->count(2))->create();
    $application = Application::factory()->create([
        'requisition_id' => $requisition->id,
        'status' => ApplicationStatusEnum::New,
    ]);

    $transition = new NewTransition($application);
    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::InReview->value,
    ]);

    expect($application->refresh()->status)
        ->toBe(ApplicationStatusEnum::InReview)
        ->and($application->current_stage_id)
        ->not->toBeNull()
        ->and($application->stageHistory)
        ->toHaveCount(1);

    $history = $application->stageHistory->first();
    expect($history->to_stage_id)
        ->toBe($application->current_stage_id)
        ->and($history->moved_by)
        ->toBe($user->id);
});

it('can transition from New to Rejected with stage history', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create(['status' => ApplicationStatusEnum::New]);

    $transition = new NewTransition($application);
    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::Rejected->value,
        'rejection_reason_category' => 'not_qualified',
        'rejection_reason_details' => 'Missing required skills',
    ]);

    expect($application->refresh()->status)
        ->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_at)
        ->not->toBeNull()
        ->and($application->rejected_by)
        ->toBe($user->id)
        ->and($application->stageHistory)
        ->toHaveCount(1);
});

it('can transition from New to Withdrawn with stage history', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create(['status' => ApplicationStatusEnum::New]);

    $transition = new NewTransition($application);
    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::Withdrawn->value,
    ]);

    expect($application->refresh()->status)
        ->toBe(ApplicationStatusEnum::Withdrawn)
        ->and($application->stageHistory)
        ->toHaveCount(1);
});

it('validates invalid transition from New', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create(['status' => ApplicationStatusEnum::New]);

    $transition = new NewTransition($application);

    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::Hired->value,
    ]);
})->throws(InvalidTransitionException::class);

it('throws error when rejection reason missing', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create(['status' => ApplicationStatusEnum::New]);

    $transition = new NewTransition($application);

    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::Rejected->value,
    ]);
})->throws(MissingTransitionDataException::class);

it('dispatches ApplicationStatusChanged event on transition', function (): void {
    Event::fake();

    $user = User::factory()->create();
    $requisition = JobRequisition::factory()->has(Stage::factory()->count(2))->create();
    $application = Application::factory()->create([
        'requisition_id' => $requisition->id,
        'status' => ApplicationStatusEnum::New,
    ]);

    $transition = new NewTransition($application);
    $transition->handle($user, [
        'to_status' => ApplicationStatusEnum::InReview->value,
    ]);

    Event::assertDispatched(fn (ApplicationStatusChanged $event) => $event->application->id === $application->id
        && $event->fromStatus === 'new'
        && $event->toStatus === 'in_review'
        && $event->by->id === $user->id);
});
