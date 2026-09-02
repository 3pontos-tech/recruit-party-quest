<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Users\User;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->team = $this->requisition->team;
    $this->stages = $this->requisition->stages()->orderBy('display_order')->get();

    $this->make = function (ApplicationStatusEnum $status, array $attributes = []): Application {
        $user = User::factory()->create(['name' => $attributes['name'] ?? fake()->name()]);
        unset($attributes['name']);

        return Application::factory()
            ->recycle($this->team)
            ->for($this->requisition, 'requisition')
            ->for(Candidate::factory()->for($user, 'user')->create(), 'candidate')
            ->create(['status' => $status, ...$attributes]);
    };
});

it('filters by status group', function (): void {
    $new = ($this->make)(ApplicationStatusEnum::New);
    $inReview = ($this->make)(ApplicationStatusEnum::InReview);
    ($this->make)(ApplicationStatusEnum::Rejected);

    expect(Application::query()->inStatusGroup(ApplicationStatusGroup::New)->pluck('id')->sort()->values()->all())
        ->toBe(collect([$new->getKey(), $inReview->getKey()])->sort()->values()->all());
});

it('filters by screening verdict', function (): void {
    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);
    $passed = ($this->make)(ApplicationStatusEnum::New);
    $failed = ($this->make)(ApplicationStatusEnum::New);
    $unanswered = ($this->make)(ApplicationStatusEnum::New);

    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $passed->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $failed->getKey(), 'question_id' => $question->getKey()]);

    expect(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Passed)->pluck('id')->all())->toBe([$passed->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Failed)->pluck('id')->all())->toBe([$failed->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::Unanswered)->pluck('id')->all())->toBe([$unanswered->getKey()])
        ->and(Application::query()->withScreeningVerdict(ScreeningVerdictFilter::All)->count())->toBe(3);

    $loaded = Application::query()->withListingCounts()->with('screeningResponses.question')->findOrFail($failed->getKey());

    expect($loaded->knockoutFailsCount())->toBe(1)
        ->and($loaded->screeningAnswersCount())->toBe(1)
        ->and($loaded->hasFailedKnockout())->toBeTrue()
        ->and($loaded->knockoutResponses()->first()?->question_id)->toBe($question->getKey());
});

it('filters by seen state', function (): void {
    $seen = ($this->make)(ApplicationStatusEnum::New);
    $unseen = ($this->make)(ApplicationStatusEnum::New);
    ApplicationView::factory()->forApplication($seen)->create();

    expect(Application::query()->withSeenState(SeenFilter::Seen)->pluck('id')->all())->toBe([$seen->getKey()])
        ->and(Application::query()->withSeenState(SeenFilter::Unseen)->pluck('id')->all())->toBe([$unseen->getKey()])
        ->and(Application::query()->withSeenState(SeenFilter::All)->count())->toBe(2);
});

it('searches by candidate name, email, headline and tracking code, case-insensitively', function (): void {
    $ana = ($this->make)(ApplicationStatusEnum::New, ['name' => 'Ana Beatriz Souza', 'tracking_code' => 'APP-1234-ZZZZ']);
    ($this->make)(ApplicationStatusEnum::New, ['name' => 'Carlos Lima', 'tracking_code' => 'APP-9999-AAAA']);

    expect(Application::query()->searchCandidate('ana beatriz')->pluck('id')->all())->toBe([$ana->getKey()])
        ->and(Application::query()->searchCandidate('1234-zzzz')->pluck('id')->all())->toBe([$ana->getKey()])
        ->and(Application::query()->searchCandidate($ana->candidate->user->email)->pluck('id')->all())->toBe([$ana->getKey()]);
});

it('orders by attention then by longest time in stage', function (): void {
    $inProgress = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(30)]);
    $newRecent = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(2)]);
    $newOld = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(10)]);

    expect(Application::query()->withStageSince()->orderForListing(ApplicationListSort::Attention)->pluck('id')->all())
        ->toBe([$newOld->getKey(), $newRecent->getKey(), $inProgress->getKey()]);
});

it('measures time in stage from the last movement or the application date', function (): void {
    $moved = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(20)]);
    $fresh = ($this->make)(ApplicationStatusEnum::New, ['created_at' => now()->subDays(20)]);

    ApplicationStageHistory::factory()->create([
        'team_id' => $this->team->getKey(),
        'application_id' => $moved->getKey(),
        'from_stage_id' => $this->stages[0]->getKey(),
        'to_stage_id' => $this->stages[1]->getKey(),
        'moved_by' => User::factory()->create()->getKey(),
        'created_at' => now()->subDays(3),
    ]);
    $moved->update(['current_stage_id' => $this->stages[1]->getKey()]);

    $rows = Application::query()->withStageSince()->orderForListing(ApplicationListSort::DaysInStage)->get();

    expect($rows->first()?->getKey())->toBe($fresh->getKey())
        ->and($rows->firstWhere('id', $moved->getKey())?->daysInStage())->toBe(3)
        ->and($rows->firstWhere('id', $fresh->getKey())?->daysInStage())->toBe(20)
        ->and($moved->fresh()->daysInStage())->toBe(3);
});

it('flags overdue applications against the expected stage duration', function (): void {
    $stage = $this->stages[1];
    $stage->update(['expected_duration_days' => 3]);

    $late = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDays(10), 'current_stage_id' => $stage->getKey()]);
    $onTime = ($this->make)(ApplicationStatusEnum::InProgress, ['created_at' => now()->subDay(), 'current_stage_id' => $stage->getKey()]);
    $closed = ($this->make)(ApplicationStatusEnum::Rejected, ['created_at' => now()->subDays(10), 'current_stage_id' => $stage->getKey()]);

    expect($late->fresh()->isOverdueInStage())->toBeTrue()
        ->and($onTime->fresh()->isOverdueInStage())->toBeFalse()
        ->and($closed->fresh()->isOverdueInStage())->toBeFalse()
        ->and($late->statusGroup())->toBe(ApplicationStatusGroup::Active);
});

it('averages only submitted evaluations', function (): void {
    $application = ($this->make)(ApplicationStatusEnum::InProgress);

    Evaluation::factory()->submitted()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::Yes]);
    Evaluation::factory()->submitted()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::StrongYes]);
    Evaluation::factory()->draft()->create(['team_id' => $this->team->getKey(), 'application_id' => $application->getKey(), 'stage_id' => $this->stages[1]->getKey(), 'overall_rating' => EvaluationRatingEnum::StrongNo]);

    expect($application->fresh()->averageEvaluationScore())->toBe(4.5)
        ->and(($this->make)(ApplicationStatusEnum::New)->averageEvaluationScore())->toBeNull();
});
