<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;

describe('AbstractApplicationTransition::handle()', function (): void {
    it('creates a stage history record after a valid transition', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::New,
        ]);

        $fromStageId = $application->current_stage_id;

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        $application->current_step->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);

        $history = ApplicationStageHistory::query()->where('application_id', $application->id)->first();

        expect($history->from_stage_id)->toBe($fromStageId)
            ->and($history->moved_by)->toBe($user->id)
            ->and($history->team_id)->toBe($application->team_id);
    });

    it('stores notes in stage history record', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::New,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
            'notes' => 'Moving to review stage.',
        ], $user->id);

        $application->current_step->handle($data);

        $history = ApplicationStageHistory::query()->where('application_id', $application->id)->first();
        expect($history->notes)->toBe('Moving to review stage.');
    });

    it('rolls back transaction and does not create history if processStep throws', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::InProgress,
        ]);

        $originalStatus = $application->status;

        // Trigger an invalid transition to force an exception inside processStep
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            // Neither to_stage_id nor advance_stage — processStep will throw
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(Exception::class);

        // DB should be untouched
        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(0);
        expect($application->fresh()->status)->toBe($originalStatus);
    });

    it('fires ApplicationStatusChanged event when status changes', function (): void {
        Event::fake([ApplicationStatusChanged::class]);

        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::New,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        $application->current_step->handle($data);

        Event::assertDispatched(fn (ApplicationStatusChanged $event): bool => $event->application->id === $application->id
            && $event->fromStatus === ApplicationStatusEnum::New->value
            && $event->toStatus === ApplicationStatusEnum::InReview->value);
    });

    it('does not fire ApplicationStatusChanged event for a stage-only move', function (): void {
        Event::fake([ApplicationStatusChanged::class]);

        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::InProgress,
        ]);
        $application->currentStage->update(['display_order' => 1, 'active' => true]);

        $newStage = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 10,
            'active' => true,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $newStage->id,
        ], $user->id);

        $application->current_step->handle($data);

        Event::assertNotDispatched(ApplicationStatusChanged::class);
    });

    it('ApplicationStatusChanged carries the correct user and meta', function (): void {
        Event::fake([ApplicationStatusChanged::class]);

        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::New,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
            'notes' => 'Test note',
        ], $user->id);

        $application->current_step->handle($data);

        Event::assertDispatched(fn (ApplicationStatusChanged $event): bool => $event->by->id === $user->id
            && $event->meta['notes'] === 'Test note');
    });

    it('ApplicationStatusChanged is dispatched after the DB transaction commits', function (): void {
        $state = new stdClass;
        $state->historyExistedAtDispatch = false;

        Event::listen(ApplicationStatusChanged::class, function (ApplicationStatusChanged $event) use ($state): void {
            $state->historyExistedAtDispatch = ApplicationStageHistory::query()
                ->where('application_id', $event->application->id)
                ->exists();
        });

        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::New,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        $application->current_step->handle($data);

        expect($state->historyExistedAtDispatch)->toBeTrue();
    });
});
