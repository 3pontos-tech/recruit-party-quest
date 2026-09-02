<?php

declare(strict_types=1);

namespace He4rt\Applications\Models;

use App\Models\BaseModel;
use He4rt\Applications\Database\Factories\ApplicationFactory;
use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;
use He4rt\Applications\Policies\ApplicationPolicy;
use He4rt\Applications\States\ApplicationState;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Concerns\InteractsWithStages;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Teams\Concerns\BelongsToTeam;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

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
 * @property ApplicationState $current_state
 * @property Carbon|null $offer_response_deadline
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, ScreeningResponse> $screeningResponses
 * @property-read Collection<int, Evaluation> $evaluations
 * @property-read JobRequisition|null $requisition
 * @property-read Stage|null $currentStage
 * @property-read ApplicationView|null $teamView
 *
 * @extends BaseModel<ApplicationFactory>
 */
#[UsePolicy(ApplicationPolicy::class)]
#[UseFactory(ApplicationFactory::class)]
class Application extends BaseModel implements Commentable
{
    use BelongsToTeam;
    use HasComments;
    use InteractsWithStages;
    use SoftDeletes;

    public const string STAGE_SINCE_SQL = 'COALESCE((SELECT MAX(h.created_at) FROM application_stage_history h WHERE h.application_id = applications.id), applications.created_at)';

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
     * @return HasOne<ApplicationView, $this>
     */
    public function teamView(): HasOne
    {
        return $this->hasOne(ApplicationView::class);
    }

    public function isSeenByTeam(): bool
    {
        if ($this->relationLoaded('teamView')) {
            return $this->teamView !== null;
        }

        return $this->teamView()->exists();
    }

    /**
     * The first active stage of the given type in this requisition (by display_order),
     * or null when the requisition has no stage of that type. Used to mirror the
     * status onto the stage at the funnel ends (offer/hired) — see
     * ApplicationState::advanceToStageType().
     */
    public function firstStageOfType(StageTypeEnum $type): ?Stage
    {
        return ($this->requisition?->stages ?? collect()) // @phpstan-ignore nullsafe.neverNull
            ->where('active', true)
            ->where('stage_type', $type)
            ->sortBy('display_order')
            ->first();
    }

    public function getNextStage(): ?Stage
    {
        $currentDisplayOrder = $this->currentStage?->display_order ?? -1; // @phpstan-ignore nullsafe.neverNull

        $stages = $this->requisition?->stages ?? collect(); // @phpstan-ignore nullsafe.neverNull
        $availableStages = $stages
            ->filter(fn (Stage $stage) => $stage->display_order > $currentDisplayOrder)
            ->sortBy('display_order');

        return $availableStages->first();
    }

    /**
     * @return Collection<int, Stage>
     */
    public function getPipelineStages(): Collection
    {
        if (! $this->requisition) {
            return new Collection();
        }

        return $this->requisition
            ->stages()
            ->orderBy('display_order')
            ->get();
    }

    public function getLastMovement(): ?ApplicationStageHistory
    {
        return $this->stageHistory()
            ->with(['toStage'])
            ->latest()
            ->first();
    }

    public function isStageCompleted(Stage $stage): bool
    {
        if (! $this->current_stage_id) {
            return false;
        }

        $currentStageOrder = $this->currentStage?->display_order ?? 0; // @phpstan-ignore nullsafe.neverNull

        return $stage->display_order < $currentStageOrder;
    }

    public function isCurrentStage(Stage $stage): bool
    {
        return $this->current_stage_id === $stage->id;
    }

    public function statusGroup(): ApplicationStatusGroup
    {
        return ApplicationStatusGroup::fromStatus($this->status);
    }

    public function stageSince(): Carbon
    {
        $selected = $this->attributes['stage_since'] ?? null;

        if (filled($selected)) {
            return new Carbon((string) $selected);
        }

        return new Carbon($this->getLastMovement()->created_at ?? $this->created_at);
    }

    public function daysInStage(): int
    {
        return (int) $this->stageSince()->diffInDays(now());
    }

    public function isOverdueInStage(): bool
    {
        $expected = $this->currentStage->expected_duration_days ?? 0;

        return $expected > 0
            && $this->statusGroup() !== ApplicationStatusGroup::Closed
            && $this->daysInStage() > $expected;
    }

    public function knockoutFailsCount(): int
    {
        $loaded = $this->attributes['knockout_fails_count'] ?? null;

        return $loaded !== null
            ? (int) $loaded
            : $this->screeningResponses()->where('is_knockout_fail', true)->count();
    }

    public function screeningAnswersCount(): int
    {
        $loaded = $this->attributes['screening_responses_count'] ?? null;

        return $loaded !== null ? (int) $loaded : $this->screeningResponses()->count();
    }

    public function hasFailedKnockout(): bool
    {
        return $this->knockoutFailsCount() > 0;
    }

    /**
     * @return Collection<int, ScreeningResponse>
     */
    public function knockoutResponses(): Collection
    {
        return $this->screeningResponses
            ->filter(fn (ScreeningResponse $response): bool => $response->question->is_knockout)
            ->sortByDesc('is_knockout_fail')
            ->values();
    }

    public function averageEvaluationScore(): ?float
    {
        $submitted = $this->evaluations->whereNotNull('submitted_at');

        if ($submitted->isEmpty()) {
            return null;
        }

        return round((float) $submitted->avg(fn (Evaluation $evaluation): int => $evaluation->overall_rating->score()), 1);
    }

    public function submittedEvaluationsCount(): int
    {
        return $this->evaluations->whereNotNull('submitted_at')->count();
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function unseenByTeam(Builder $query): Builder
    {
        return $query->whereDoesntHave('teamView');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function seenByTeam(Builder $query): Builder
    {
        return $query->whereHas('teamView');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withStageSince(Builder $query): Builder
    {
        return $query->select('applications.*')->selectRaw(self::STAGE_SINCE_SQL.' AS stage_since');
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function searchCandidate(Builder $query, string $term): Builder
    {
        $like = '%'.mb_trim($term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query
                ->where('applications.tracking_code', 'ilike', $like)
                ->orWhereHas('candidate', fn (Builder $candidate) => $candidate
                    ->where('headline', 'ilike', $like)
                    ->orWhereHas('user', fn (Builder $user) => $user
                        ->where('name', 'ilike', $like)
                        ->orWhere('email', 'ilike', $like)));
        });
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function inStatusGroup(Builder $query, ApplicationStatusGroup $group): Builder
    {
        return $query->whereIn('applications.status', $group->values());
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withScreeningVerdict(Builder $query, ScreeningVerdictFilter $filter): Builder
    {
        $failed = fn (Builder $responses): Builder => $responses->where('is_knockout_fail', true);

        return match ($filter) {
            ScreeningVerdictFilter::Passed => $query->whereHas('screeningResponses')->whereDoesntHave('screeningResponses', $failed),
            ScreeningVerdictFilter::Failed => $query->whereHas('screeningResponses', $failed),
            ScreeningVerdictFilter::Unanswered => $query->whereDoesntHave('screeningResponses'),
            ScreeningVerdictFilter::All => $query,
        };
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withSeenState(Builder $query, SeenFilter $filter): Builder
    {
        return match ($filter) {
            SeenFilter::Unseen => $query->whereDoesntHave('teamView'),
            SeenFilter::Seen => $query->whereHas('teamView'),
            SeenFilter::All => $query,
        };
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function withListingCounts(Builder $query): Builder
    {
        return $query->withCount([
            'comments',
            'screeningResponses',
            'screeningResponses as knockout_fails_count' => fn (Builder $responses) => $responses->where('is_knockout_fail', true),
        ]);
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Scope]
    protected function orderForListing(Builder $query, ApplicationListSort $sort): Builder
    {
        $ordered = match ($sort) {
            ApplicationListSort::Name => $query->orderBy(
                User::query()
                    ->select('users.name')
                    ->join('candidates', 'candidates.user_id', '=', 'users.id')
                    ->whereColumn('candidates.id', 'applications.candidate_id')
                    ->limit(1)
            ),
            ApplicationListSort::Applied => $query->latest('applications.created_at'),
            ApplicationListSort::DaysInStage => $query->orderByRaw(self::STAGE_SINCE_SQL.' ASC'),
            ApplicationListSort::Stage => $query->orderByDesc(
                Stage::query()->select('display_order')->whereColumn('id', 'applications.current_stage_id')->limit(1)
            ),
            ApplicationListSort::Attention => $query->orderByRaw($this->attentionOrderSql())->orderByRaw(self::STAGE_SINCE_SQL.' ASC'),
        };

        return $ordered->orderBy('applications.id');
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

    /**
     * @phpstan-ignore missingType.generics
     */
    protected function currentState(): Attribute
    {
        return Attribute::make(get: fn () => $this->status->state($this));
    }

    private function attentionOrderSql(): string
    {
        $ranks = [
            ApplicationStatusEnum::New->value => 0,
            ApplicationStatusEnum::InReview->value => 1,
            ApplicationStatusEnum::InProgress->value => 2,
            ApplicationStatusEnum::OfferExtended->value => 3,
            ApplicationStatusEnum::OfferAccepted->value => 4,
            ApplicationStatusEnum::Hired->value => 5,
        ];

        $cases = collect($ranks)
            ->map(fn (int $rank, string $status): string => sprintf("WHEN '%s' THEN %d", $status, $rank))
            ->implode(' ');

        return sprintf('CASE applications.status %s ELSE %d END', $cases, count($ranks));
    }
}
