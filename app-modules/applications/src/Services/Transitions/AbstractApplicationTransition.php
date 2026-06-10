<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Events\ApplicationStatusChanged;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
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

        DB::transaction(function () use ($data, $fromStage, $fromStatus): void {
            $this->validate($data);
            $this->processStep($data);
            $this->notify($data);

            $this->application->stageHistory()->create([
                'from_stage_id' => $fromStage,
                'to_stage_id' => $this->application->current_stage_id,
                'from_status' => $fromStatus,
                'to_status' => $this->application->status->value,
                'moved_by' => $data->byUserId,
                'notes' => $data->notes,
                'team_id' => $this->application->team_id,
            ]);
        });

        $toStatus = $this->application->refresh()->status->value;

        // TODO (issue #158): ApplicationStatusChanged ainda não possui listeners de
        // notificação. by === null indica transição automática (sistema).
        if ($fromStatus !== $toStatus) {
            $by = $data->byUserId !== null
                ? User::query()->findOrFail($data->byUserId)
                : null;

            event(new ApplicationStatusChanged(
                $this->application,
                $fromStatus,
                $toStatus,
                $by,
                $data->toArray()
            ));
        }
    }

    /**
     * Mirror the status onto the stage at the funnel ends: move the application to
     * the requisition's stage of the given type (offer/hired). Deterministic and
     * null-safe:
     *  - already on a stage of that type → no move (covers duplicate stages);
     *  - a matching stage exists → move to the first active one (by display_order);
     *  - no matching stage (admin removed it) → status-only, never throws.
     */
    protected function advanceToStageType(StageTypeEnum $type): void
    {
        if ($this->application->currentStage?->stage_type === $type) {
            return;
        }

        $target = $this->application->firstStageOfType($type);

        if ($target instanceof Stage) {
            $this->application->update(['current_stage_id' => $target->getKey()]);
        }
    }
}
