<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Support\ApplicationNavigationContext;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
});

function makeApplication(JobRequisition $requisition, ApplicationStatusEnum $status, string $createdAt): Application
{
    return Application::factory()
        ->for($requisition, 'requisition')
        ->state([
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])
        ->create();
}

it('computes position and total within the active set of the requisition', function (): void {
    $apps = collect([
        '2026-05-01 10:00:00',
        '2026-05-02 10:00:00',
        '2026-05-03 10:00:00',
        '2026-05-04 10:00:00',
        '2026-05-05 10:00:00',
    ])->map(fn (string $ts) => makeApplication($this->requisition, ApplicationStatusEnum::InReview, $ts));

    $context = ApplicationNavigationContext::forApplication($apps[2]);

    expect($context->position)->toBe(3);
    expect($context->total)->toBe(5);
    expect($context->previous?->id)->toBe($apps[1]->id);
    expect($context->next?->id)->toBe($apps[3]->id);
    expect($context->shouldRender())->toBeTrue();
});

it('excludes rejected and withdrawn from the active set', function (): void {
    $active1 = makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');
    makeApplication($this->requisition, ApplicationStatusEnum::Rejected, '2026-05-02 10:00:00');
    makeApplication($this->requisition, ApplicationStatusEnum::Withdrawn, '2026-05-03 10:00:00');
    $active2 = makeApplication($this->requisition, ApplicationStatusEnum::New, '2026-05-04 10:00:00');

    $context = ApplicationNavigationContext::forApplication($active1);

    expect($context->total)->toBe(2);
    expect($context->position)->toBe(1);
    expect($context->next?->id)->toBe($active2->id);
});

it('returns shouldRender false when current record is outside the active set', function (): void {
    makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');
    $rejected = makeApplication($this->requisition, ApplicationStatusEnum::Rejected, '2026-05-02 10:00:00');
    makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-03 10:00:00');

    $context = ApplicationNavigationContext::forApplication($rejected);

    expect($context->position)->toBeNull();
    expect($context->total)->toBe(2);
    expect($context->shouldRender())->toBeFalse();
});

it('returns shouldRender false when only one active candidature exists', function (): void {
    $only = makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');

    $context = ApplicationNavigationContext::forApplication($only);

    expect($context->position)->toBe(1);
    expect($context->total)->toBe(1);
    expect($context->shouldRender())->toBeFalse();
    expect($context->previous)->toBeNull();
    expect($context->next)->toBeNull();
});

it('disables previous on the first record and next on the last', function (): void {
    $first = makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');
    makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-02 10:00:00');
    $last = makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-03 10:00:00');

    $firstContext = ApplicationNavigationContext::forApplication($first);
    expect($firstContext->previous)->toBeNull();
    expect($firstContext->next)->not->toBeNull();

    $lastContext = ApplicationNavigationContext::forApplication($last);
    expect($lastContext->previous)->not->toBeNull();
    expect($lastContext->next)->toBeNull();
});

it('breaks created_at ties using id', function (): void {
    $sharedTimestamp = '2026-05-01 10:00:00';
    $a = makeApplication($this->requisition, ApplicationStatusEnum::InReview, $sharedTimestamp);
    $b = makeApplication($this->requisition, ApplicationStatusEnum::InReview, $sharedTimestamp);

    [$lower, $higher] = $a->id < $b->id ? [$a, $b] : [$b, $a];

    $context = ApplicationNavigationContext::forApplication($lower);
    expect($context->next?->id)->toBe($higher->id);
});

it('isolates the navigable set per requisition', function (): void {
    $otherRequisition = JobRequisition::factory()->create();
    $inV1 = makeApplication($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');
    makeApplication($otherRequisition, ApplicationStatusEnum::InReview, '2026-05-02 10:00:00');
    makeApplication($otherRequisition, ApplicationStatusEnum::InReview, '2026-05-03 10:00:00');

    $context = ApplicationNavigationContext::forApplication($inV1);

    expect($context->total)->toBe(1);
    expect($context->allActive)->toHaveCount(1);
});
