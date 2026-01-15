<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Concerns;

use He4rt\Recruitment\Stages\Models\InterviewerPivot;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait InteractsWithInterviewStages
{
    /**
     * @return BelongsToMany<Stage, $this, InterviewerPivot>
     */
    public function stages(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Stage::class,
                'recruitment_stage_interviewer',
                'interviewer_user_id',
                'pipeline_stage_id'
            )->withTimestamps()
            ->using(InterviewerPivot::class);
    }
}
