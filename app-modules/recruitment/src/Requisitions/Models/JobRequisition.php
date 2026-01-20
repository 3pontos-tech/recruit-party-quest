<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Database\Factories\JobRequisitionFactory;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Policies\JobRequisitionPolicy;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $team_id
 * @property string $department_id
 * @property string $work_arrangement
 * @property string $employment_type
 * @property string $experience_level
 * @property string $positions_available
 * @property int|null $salary_range_min
 * @property int|null $salary_range_max
 * @property string $salary_currency
 * @property string $hiring_manager_id
 * @property string $created_by_id
 * @property RequisitionStatusEnum $status
 * @property string $priority
 * @property Carbon|null $target_start_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $published_at
 * @property Carbon|null $closed_at
 * @property bool $is_internal_only
 * @property bool $is_confidential
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Team $team
 * @property-read Department $department
 * @property-read User $hiringManager
 * @property-read User $createdBy
 * @property-read Collection<int, ScreeningQuestion> $screeningQuestions
 * @property-read Collection<int, Stage> $stages
 * @property-read JobPosting $post
 *
 * @extends BaseModel<JobRequisitionFactory>
 */
#[UseFactory(JobRequisitionFactory::class)]
#[UsePolicy(JobRequisitionPolicy::class)]
class JobRequisition extends BaseModel
{
    use BelongsToTeam;
    use SoftDeletes;

    protected $table = 'recruitment_job_requisitions';

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasOne<JobPosting, $this>
     */
    public function post(): HasOne
    {
        return $this->hasOne(JobPosting::class, 'job_requisition_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function hiringManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hiring_manager_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return MorphMany<ScreeningQuestion, $this>
     */
    public function screeningQuestions(): MorphMany
    {
        return $this->morphMany(ScreeningQuestion::class, 'screenable');
    }

    /**
     * @return HasMany<Stage, $this>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'job_requisition_id');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'requisition_id');
    }

    protected function casts(): array
    {
        return [
            'target_start_at' => 'timestamp',
            'approved_at' => 'timestamp',
            'published_at' => 'timestamp',
            'closed_at' => 'timestamp',
            'is_internal_only' => 'boolean',
            'is_confidential' => 'boolean',
            'status' => RequisitionStatusEnum::class,
            'priority' => RequisitionPriorityEnum::class,
            'work_arrangement' => WorkArrangementEnum::class,
            'employment_type' => EmploymentTypeEnum::class,
            'experience_level' => ExperienceLevelEnum::class,
        ];
    }
}
