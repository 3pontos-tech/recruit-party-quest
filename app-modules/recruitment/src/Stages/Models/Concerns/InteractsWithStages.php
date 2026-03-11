<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Models\Concerns;

use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property-read Stage|null $last_stage
 * @property-read Stage|null $first_stage
 * @property-read bool $is_last_stage
 */
trait InteractsWithStages
{
    public function getNextStage(): ?Stage
    {
        $currentDisplayOrder = $this->currentStage?->display_order ?? -1;

        $stages = $this->requisition?->stages ?? collect();
        $availableStages = $stages
            ->filter(fn (Stage $stage) => $stage->display_order > $currentDisplayOrder)
            ->sortBy('display_order');

        return $availableStages->first();
    }

    /**
     * @return Attribute<Stage|null, never>
     */
    protected function firstStage(): Attribute
    {
        return Attribute::make(get: fn () => ($this->requisition?->stages ?? collect())->sortBy('display_order')->first()); // @phpstan-ignore nullsafe.neverNull
    }

    /**
     * @return Attribute<Stage|null, never>
     */
    protected function lastStage(): Attribute
    {
        return Attribute::make(get: fn () => ($this->requisition?->stages ?? collect())->sortBy('display_order')->last()); // @phpstan-ignore nullsafe.neverNull
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isLastStage(): Attribute
    {
        return Attribute::make(get: fn () => $this->last_stage?->getKey() === $this->current_stage_id);
    }
}
