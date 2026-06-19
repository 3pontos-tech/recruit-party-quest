<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Models\Application;
use He4rt\Applications\States\TransitionData;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;

it('performs a New → Rejected transition with a null actor (system)', function (): void {
    $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout,
        'rejection_reason_details' => 'Failed: Q1',
    ]);

    $application->current_state->handle($data);

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_by)->toBeNull()
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout);

    $movement = $application->getLastMovement();
    expect($movement->moved_by)->toBeNull();
});

it('still dispatches ApplicationStatusChanged with a null actor', function (): void {
    Event::fake([ApplicationStatusChanged::class]);

    $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

    $data = TransitionData::fromArray([
        'to_status' => ApplicationStatusEnum::Withdrawn,
    ]);

    $application->current_state->handle($data);

    Event::assertDispatched(fn (ApplicationStatusChanged $event): bool => ! $event->by instanceof User
        && $event->toStatus === ApplicationStatusEnum::Withdrawn->value);
});
