<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Models;

use App\Models\BasePivot;
use He4rt\Recruitment\Database\Factories\InterviewerPivotFactory;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $pipeline_stage_id
 * @property string $recruiter_id
 * @property-read Stage $stage
 * @property-read Recruiter $interviewer
 *
 * @extends BasePivot<InterviewerPivotFactory>
 */
#[UseFactory(InterviewerPivotFactory::class)]
class InterviewerPivot extends BasePivot
{
    protected $table = 'recruitment_stage_interviewer';

    /**
     * @return BelongsTo<Recruiter, $this>
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(Recruiter::class, 'recruiter_id', 'id');
    }

    /**
     * @return BelongsTo<Stage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'pipeline_stage_id', 'id');
    }
}
