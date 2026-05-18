<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\ScreeningResponse\EvaluateScreeningResponses;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Events\ScreeningEvaluated;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->application = Application::factory()->create([
        'requisition_id' => $this->requisition->id,
        'team_id' => $this->requisition->team_id,
    ]);
});

function knockoutQuestion(JobRequisition $req, array $criteria): ScreeningQuestion
{
    return ScreeningQuestion::factory()
        ->for($req, 'screenable')
        ->state([
            'team_id' => $req->team_id,
            'question_type' => QuestionTypeEnum::YesNo,
            'settings' => [],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => $criteria,
        ])
        ->create();
}

it('marks is_knockout_fail true when the answer fails the criteria', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = knockoutQuestion($this->requisition, ['expected' => 'yes']);

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'no'],
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeTrue();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed && $e->hadKnockoutCriteria);
});

it('keeps is_knockout_fail false when the answer passes', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = knockoutQuestion($this->requisition, ['expected' => 'yes']);

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'yes'],
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('clears a stale is_knockout_fail when a re-evaluation now passes', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = knockoutQuestion($this->requisition, ['expected' => 'yes']);

    // The response previously failed and was persisted as a knockout fail.
    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'yes'],
        'is_knockout_fail' => true,
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    // The answer now passes, so the stale failure flag must be cleared.
    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('reports hadKnockoutCriteria false when there are no knockout questions', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    ScreeningQuestion::factory()
        ->for($this->requisition, 'screenable')
        ->state([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::Text,
            'settings' => [],
            'is_knockout' => false,
            'knockout_criteria' => null,
        ])
        ->create();

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria === false);
});

it('treats a missing answer to a knockout question as a pass', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    knockoutQuestion($this->requisition, ['expected' => 'yes']);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('does not knock out a candidate when accepted criteria references options that no longer exist (EC-2)', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    // Recruiter set accepted=['Senior'] then renamed the option to 'jr'.
    $question = ScreeningQuestion::factory()
        ->for($this->requisition, 'screenable')
        ->state([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::SingleChoice,
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['value' => 'jr', 'label' => 'Junior'],
                ],
            ],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => ['accepted' => ['Senior']], // stale
        ])
        ->create();

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'jr'], // valid current choice
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    // Stale criteria must NOT reject the candidate.
    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false);
});

it('still evaluates against the accepted options that remain valid (EC-2 partial)', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    $question = ScreeningQuestion::factory()
        ->for($this->requisition, 'screenable')
        ->state([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::SingleChoice,
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['value' => 'a', 'label' => 'A'],
                    ['value' => 'b', 'label' => 'B'],
                ],
            ],
            'is_required' => true,
            'is_knockout' => true,
            'knockout_criteria' => ['accepted' => ['a', 'gone']], // 'gone' stale, 'a' valid
        ])
        ->create();

    $response = ScreeningResponse::query()->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
        'response_value' => ['value' => 'b'], // not in remaining valid accepted ['a'] → fails
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeTrue();
});
