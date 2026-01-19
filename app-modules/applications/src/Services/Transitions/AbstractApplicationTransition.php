<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Models\Application;
use He4rt\Users\User;
use Illuminate\Support\Facades\DB;
use Throwable;

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
     * Perform the domain update for this step (should persist model changes)
     *
     * @param  array<string,mixed>  $meta
     */
    abstract public function processStep(array $meta = []): void;

    /**
     * Notify after successful process (send emails, push notifications, etc.)
     *
     * @param  array<string,mixed>  $meta
     */
    abstract public function notify(array $meta = []): void;

    /**
     * Whether this transition can be triggered (additional guard)
     */
    abstract public function canChange(): bool;

    /**
     * Handle the transition: processStep, notify, dispatch event
     *
     * @param  array<string,mixed>  $meta
     */
    public function handle(array $meta = [], ?User $by = null): void
    {
        try {
            DB::transaction(function () use ($meta, $by): void {
                $data = array_merge($meta, ['by_user_id' => $by?->id ?? $meta['by_user_id'] ?? null]);
                $this->processStep($data);
                $this->notify($data);
            });
        } catch (Throwable) {
            // Todo: notify about failure
        }

    }
}
