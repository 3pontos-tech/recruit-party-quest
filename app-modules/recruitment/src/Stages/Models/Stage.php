<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Models;

use App\Models\BaseModel;
use He4rt\Recruitment\Database\Factories\StageFactory;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $job_requisition_id
 * @property string $name
 * @property string $stage_type
 * @property int $display_order
 * @property string $description
 * @property int $expected_duration_days
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @extends BaseModel<StageFactory>
 */
class Stage extends BaseModel
{
    use SoftDeletes;

    protected $table = 'recruitment_pipeline_stages';

    /**
     * @return BelongsTo<JobRequisition, $this>
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_requisition_id');
    }

    /**
     * @return BelongsToMany<User, $this, InterviewerPivot>
     */
    public function interviewers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'recruitment_stage_interviewer',
            'pipeline_stage_id',
            'interviewer_user_id',
        )->withTimestamps()
            ->using(InterviewerPivot::class);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'stage_type' => StageTypeEnum::class,
        ];
    }
}
