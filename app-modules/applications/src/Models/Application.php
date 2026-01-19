<?php

declare(strict_types=1);

namespace He4rt\Applications\Models;

use App\Models\BaseModel;
use He4rt\Applications\Database\Factories\ApplicationFactory;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Policies\ApplicationPolicy;
use He4rt\Applications\Services\Transitions\AbstractApplicationTransition;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $requisition_id
 * @property string $candidate_id
 * @property string|null $current_stage_id
 * @property ApplicationStatusEnum $status
 * @property CandidateSourceEnum $source
 * @property string|null $source_details
 * @property string|null $cover_letter
 * @property string|null $tracking_code
 * @property Carbon|null $rejected_at
 * @property string|null $rejected_by
 * @property RejectionReasonCategoryEnum|null $rejection_reason_category
 * @property string|null $rejection_reason_details
 * @property Carbon|null $offer_extended_at
 * @property string|null $offer_extended_by
 * @property float|null $offer_amount
 * @property AbstractApplicationTransition $current_step
 * @property Carbon|null $offer_response_deadline
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, ScreeningResponse> $screeningResponses
 * @property-read Collection<int, Evaluation> $evaluations
 * @property-read Collection<int, ApplicationComment> $comments
 * @property-read JobRequisition $requisition
 *
 * @extends BaseModel<ApplicationFactory>
 */
#[UsePolicy(ApplicationPolicy::class)]
#[UseFactory(ApplicationFactory::class)]
class Application extends BaseModel
{
    use BelongsToTeam;

    /**
     * @return BelongsTo<JobRequisition, $this>
     */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'requisition_id');
    }

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * @return BelongsTo<Stage, $this>
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function offerExtendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offer_extended_by');
    }

    /**
     * @return HasMany<ApplicationStageHistory, $this>
     */
    public function stageHistory(): HasMany
    {
        return $this->hasMany(ApplicationStageHistory::class);
    }

    /**
     * @return HasMany<ScreeningResponse, $this>
     */
    public function screeningResponses(): HasMany
    {
        return $this->hasMany(ScreeningResponse::class);
    }

    /**
     * @return HasMany<Evaluation, $this>
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * @return HasMany<ApplicationComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ApplicationComment::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatusEnum::class,
            'source' => CandidateSourceEnum::class,
            'rejection_reason_category' => RejectionReasonCategoryEnum::class,
            'rejected_at' => 'datetime',
            'offer_extended_at' => 'datetime',
            'offer_response_deadline' => 'datetime',
            'offer_amount' => 'decimal:2',
        ];
    }

    protected function currentStep(): Attribute
    {
        return Attribute::make(get: fn () => $this->status->getAction($this));
    }
}
