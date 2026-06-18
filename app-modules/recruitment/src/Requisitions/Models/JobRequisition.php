<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Models;

use AlizHarb\ActivityLog\Contracts\HasActivityLogTitle;
use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Database\Factories\JobRequisitionFactory;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\JobCategoryEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use He4rt\Recruitment\Requisitions\Observers\JobRequisitionObserver;
use He4rt\Recruitment\Requisitions\Policies\JobRequisitionPolicy;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $slug
 * @property string $team_id
 * @property string $department_id
 * @property WorkArrangementEnum $work_arrangement
 * @property EmploymentTypeEnum|null $employment_type
 * @property WorkScheduleEnum|null $work_schedule
 * @property ExperienceLevelEnum $experience_level
 * @property JobCategoryEnum|null $category
 * @property int $positions_available
 * @property int|null $salary_range_min
 * @property int|null $salary_range_max
 * @property bool $show_salary_to_candidates
 * @property string $salary_currency
 * @property string $recruiter_id
 * @property string $created_by_id
 * @property RequisitionStatusEnum $status
 * @property string $priority
 * @property Carbon|null $target_start_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $published_at
 * @property Carbon|null $closed_at
 * @property bool $is_internal_only
 * @property bool $is_confidential
 * @property bool $auto_screening_transition
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Team $team
 * @property-read Department $department
 * @property-read Recruiter $recruiter
 * @property-read User $createdBy
 * @property-read Collection<int, ScreeningQuestion> $screeningQuestions
 * @property-read Collection<int, Stage> $stages
 * @property-read Collection<int, JobRequisitionItem> $items
 * @property-read JobPosting|null $post
 *
 * @extends BaseModel<JobRequisitionFactory>
 */
#[UseFactory(JobRequisitionFactory::class)]
#[UsePolicy(JobRequisitionPolicy::class)]
#[ObservedBy(JobRequisitionObserver::class)]
class JobRequisition extends BaseModel implements HasActivityLogTitle
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
     * @return HasMany<JobRequisitionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(JobRequisitionItem::class)->orderBy('order');
    }

    /**
     * @return BelongsTo<Recruiter, $this>
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(Recruiter::class);
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

    /**
     * The candidate's application for this requisition, if any.
     *
     * Single source of truth for the "has this candidate already applied?" check
     * shared by the apply guard, the page redirect, and the job description view.
     */
    public function applicationFrom(Candidate $candidate): ?Application
    {
        return $this->applications()
            ->where('candidate_id', $candidate->getKey())
            ->first();
    }

    public function getNextStage(Stage $currentStage): ?Stage
    {
        $availableStages = $this
            ->stages
            ->filter(fn (Stage $stage) => $stage->display_order > $currentStage->display_order);

        if ($availableStages->isEmpty()) {
            return null;
        }

        return $availableStages->first();
    }

    public function getActivityLogTitle(): string
    {
        return 'Job Requisition'.$this->team_id;
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function salaryRangeForCandidates(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            if (! $this->show_salary_to_candidates
                || is_null($this->salary_range_min)
                || is_null($this->salary_range_max)) {
                return null;
            }

            return sprintf(
                '%s %s - %s',
                $this->salary_currency,
                number_format($this->salary_range_min, 0, ',', '.'),
                number_format($this->salary_range_max, 0, ',', '.'),
            );
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function hasStages(Builder $query): Builder
    {
        return $query->has('stages');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function publicJobs(Builder $query): Builder
    {
        return $query->where('is_internal_only', false);
    }

    protected function casts(): array
    {
        return [
            'positions_available' => 'integer',
            'target_start_at' => 'timestamp',
            'approved_at' => 'timestamp',
            'published_at' => 'timestamp',
            'closed_at' => 'timestamp',
            'is_internal_only' => 'boolean',
            'is_confidential' => 'boolean',
            'auto_screening_transition' => 'boolean',
            'status' => RequisitionStatusEnum::class,
            'priority' => RequisitionPriorityEnum::class,
            'work_arrangement' => WorkArrangementEnum::class,
            'employment_type' => EmploymentTypeEnum::class,
            'work_schedule' => WorkScheduleEnum::class,
            'experience_level' => ExperienceLevelEnum::class,
            'category' => JobCategoryEnum::class,
        ];
    }
}
