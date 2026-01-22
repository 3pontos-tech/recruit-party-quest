<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Models\Concerns;

use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property-read Stage $last_stage
 * @property-read bool $is_last_stage
 */
trait InteractsWithStages
{
    public function getNextStage(): ?Stage
    {
        $currentDisplayOrder = $this->currentStage->display_order ?? -1;

        $availableStages = $this
            ->requisition
            ->stages
            ->filter(fn (Stage $stage) => $stage->display_order > $currentDisplayOrder)
            ->sortBy('display_order');

        return $availableStages->first();
    }

    /**
     * @return Attribute<Stage, never>
     */
    protected function lastStage(): Attribute
    {
        return Attribute::make(get: fn () => $this->requisition->stages->last());
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isLastStage(): Attribute
    {
        return Attribute::make(get: fn () => $this->last_stage->getKey() === $this->current_stage_id);
    }
}
