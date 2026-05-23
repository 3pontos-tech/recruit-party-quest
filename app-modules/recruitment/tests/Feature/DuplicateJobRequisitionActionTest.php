<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Actions\DuplicateJobRequisitionAction;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisitionItem;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Users\User;

beforeEach(function (): void {
    $this->original = JobRequisition::factory()->create([
        'status' => RequisitionStatusEnum::Published,
    ]);

    // The observer seeds 8 default stages; replace them with a custom pipeline.
    $this->original->stages()->forceDelete();

    $this->stages = Stage::factory()
        ->count(3)
        ->for($this->original, 'requisition')
        ->ordered()
        ->create(['team_id' => $this->original->team_id]);

    $this->stageWithExtras = $this->stages->first();

    $this->interviewers = Recruiter::factory()->count(2)->create(['team_id' => $this->original->team_id]);
    $this->stageWithExtras->interviewers()->attach($this->interviewers->pluck('id'));

    ScreeningQuestion::factory()->create([
        'team_id' => $this->original->team_id,
        'screenable_id' => $this->stageWithExtras->getKey(),
        'screenable_type' => $this->stageWithExtras->getMorphClass(),
        'display_order' => 1,
    ]);

    $this->post = JobPosting::factory()
        ->for($this->original, 'jobRequisition')
        ->create(['title' => 'Senior PHP Engineer']);

    JobRequisitionItem::factory()
        ->count(3)
        ->for($this->original, 'jobRequisition')
        ->create();

    $this->taggedItem = JobRequisitionItem::factory()
        ->withTags(['php', 'laravel'])
        ->for($this->original, 'jobRequisition')
        ->create([
            'type' => JobRequisitionItemTypeEnum::Responsibility,
            'content' => 'Tagged responsibility unique content',
        ]);

    ScreeningQuestion::factory()
        ->count(2)
        ->sequence(['display_order' => 1], ['display_order' => 2])
        ->create([
            'team_id' => $this->original->team_id,
            'screenable_id' => $this->original->getKey(),
            'screenable_type' => $this->original->getMorphClass(),
        ]);

    $this->actor = User::factory()->create();
});

it('creates the duplicate as a draft owned by the acting user', function (): void {
    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    expect($duplicate->getKey())->not->toBe($this->original->getKey())
        ->and($duplicate->status)->toBe(RequisitionStatusEnum::Draft)
        ->and($duplicate->created_by_id)->toBe($this->actor->getKey())
        ->and($duplicate->approved_at)->toBeNull()
        ->and($duplicate->published_at)->toBeNull()
        ->and($duplicate->closed_at)->toBeNull()
        ->and($duplicate->target_start_at)->toBeNull();
});

it('generates a new unique slug following the team pattern', function (): void {
    $originalSlug = $this->original->slug;

    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    expect($duplicate->slug)->not->toBe($originalSlug)
        ->and($duplicate->slug)->toMatch('/-'.preg_quote((string) $this->original->team->slug, '/').'-[a-z0-9]{5}$/');
});

it('produces distinct slugs when the same requisition is duplicated twice', function (): void {
    $action = resolve(DuplicateJobRequisitionAction::class);

    $first = $action->execute($this->original, (string) $this->actor->getKey());
    $second = $action->execute($this->original, (string) $this->actor->getKey());

    expect($first->getKey())->not->toBe($second->getKey())
        ->and($first->slug)->not->toBe($second->slug)
        ->and(JobRequisition::query()->whereIn('id', [$first->getKey(), $second->getKey()])->count())->toBe(2);
});

it('copies the job posting with the copy suffix and matching slug', function (): void {
    $suffix = __('panel-organization::filament.actions.duplicate_job_requisition.title_suffix');

    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    expect($duplicate->post)->not->toBeNull()
        ->and($duplicate->post->title)->toBe('Senior PHP Engineer '.$suffix)
        ->and($duplicate->post->slug)->toBe($duplicate->slug)
        ->and($duplicate->post->getKey())->not->toBe($this->post->getKey());
});

it('copies requisition items together with their tags', function (): void {
    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    $clonedTaggedItem = $duplicate->items->firstWhere('content', 'Tagged responsibility unique content');

    expect($duplicate->items)->toHaveCount(4)
        ->and($clonedTaggedItem)->not->toBeNull()
        ->and($clonedTaggedItem->getKey())->not->toBe($this->taggedItem->getKey())
        ->and($clonedTaggedItem->tags->pluck('name'))->toHaveCount(2);
});

it('copies requisition-level screening questions', function (): void {
    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    expect($duplicate->screeningQuestions)->toHaveCount(2)
        ->and($duplicate->screeningQuestions->pluck('id'))
        ->not->toContain(...$this->original->screeningQuestions->pluck('id')->all());
});

it('mirrors the original pipeline stages instead of the default ones', function (): void {
    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    $clonedStageWithExtras = $duplicate->stages->firstWhere('display_order', 1);

    expect($duplicate->stages)->toHaveCount(3)
        ->and($duplicate->stages->pluck('display_order')->sort()->values()->all())->toBe([1, 2, 3])
        ->and($clonedStageWithExtras->screeningQuestions)->toHaveCount(1)
        ->and($clonedStageWithExtras->interviewers->pluck('id')->sort()->values()->all())
        ->toBe($this->interviewers->pluck('id')->sort()->values()->all());
});

it('does not copy applications', function (): void {
    Application::factory()->for($this->original, 'requisition')->create();

    $applicationCountBefore = Application::query()->count();

    $duplicate = resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    expect($duplicate->applications()->count())->toBe(0)
        ->and(Application::query()->count())->toBe($applicationCountBefore);
});

it('leaves the original requisition untouched', function (): void {
    $originalSlug = $this->original->slug;

    resolve(DuplicateJobRequisitionAction::class)
        ->execute($this->original, (string) $this->actor->getKey());

    $fresh = $this->original->fresh();

    expect($fresh->status)->toBe(RequisitionStatusEnum::Published)
        ->and($fresh->slug)->toBe($originalSlug)
        ->and($fresh->stages()->count())->toBe(3)
        ->and($fresh->items()->count())->toBe(4);
});
