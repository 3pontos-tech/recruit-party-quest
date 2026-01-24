<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Database\Factories\StageFactory;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $job_requisition_id
 * @property string $name
 * @property StageTypeEnum $stage_type
 * @property int $display_order
 * @property string $description
 * @property int $expected_duration_days
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Application> $participants
 *
 * @extends BaseModel<StageFactory>
 */
#[UseFactory(StageFactory::class)]
class Stage extends BaseModel
{
    use BelongsToTeam;
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
     * @return MorphMany<ScreeningQuestion, $this>
     */
    public function screeningQuestions(): MorphMany
    {
        return $this->morphMany(ScreeningQuestion::class, 'screenable');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Application::class, 'current_stage_id');
    }

    /**
     * @return BelongsToMany<Recruiter, $this, InterviewerPivot>
     */
    public function interviewers(): BelongsToMany
    {
        return $this->belongsToMany(
            Recruiter::class,
            'recruitment_stage_interviewer',
            'pipeline_stage_id',
            'recruiter_id',
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
