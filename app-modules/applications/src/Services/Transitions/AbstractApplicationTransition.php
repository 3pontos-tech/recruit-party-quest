<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Models\Application;
use He4rt\Users\User;
use Illuminate\Support\Facades\DB;

abstract class AbstractApplicationTransition
{
    public function __construct(public Application $application) {}

    /**
     * Get the possible choices for the current step.
     *
     * @return array<string, string|null>
     */
    abstract public function choices(): array;

    /**
     * Perform domain update for this step (should persist model changes)
     */
    abstract public function processStep(TransitionData $data): void;

    /**
     * Notify after successful process (send emails, push notifications, etc.)
     */
    abstract public function notify(TransitionData $data): void;

    /**
     * Whether this transition can be triggered (additional guard)
     */
    abstract public function canChange(): bool;

    /**
     * Validate transition parameters before processing
     */
    abstract public function validate(TransitionData $data): void;

    /**
     * Handle the transition: validate, processStep, notify, create stage history, dispatch event
     */
    public function handle(TransitionData $data): void
    {
        $fromStatus = $this->application->status->value;
        $fromStage = $this->application->current_stage_id;

        DB::transaction(function () use ($data, $fromStage): void {
            $this->validate($data);
            $this->processStep($data);
            $this->notify($data);

            $this->application->stageHistory()->create([
                'from_stage_id' => $fromStage,
                'to_stage_id' => $this->application->current_stage_id,
                'moved_by' => $data->byUserId,
                'notes' => $data->notes,
                'team_id' => $this->application->team_id,
            ]);
        });

        $toStatus = $this->application->refresh()->status->value;

        if ($fromStatus !== $toStatus && $data->isStatusChange()) {
            event(new ApplicationStatusChanged(
                $this->application,
                $fromStatus,
                $toStatus,
                User::query()->findOrFail($data->byUserId),
                $data->toArray()
            ));
        }
    }
}
