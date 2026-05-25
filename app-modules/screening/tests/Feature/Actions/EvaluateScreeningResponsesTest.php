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

it('marks is_knockout_fail true when the answer fails the criteria', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = ScreeningQuestion::factory()->yesNo()->knockout(['expected' => 'yes'])
        ->for($this->requisition, 'screenable')
        ->create(['team_id' => $this->requisition->team_id]);

    $response = ScreeningResponse::factory()->yesNoResponse(false)->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeTrue();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed && $e->hadKnockoutCriteria);
});

it('keeps is_knockout_fail false when the answer passes', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = ScreeningQuestion::factory()->yesNo()->knockout(['expected' => 'yes'])
        ->for($this->requisition, 'screenable')
        ->create(['team_id' => $this->requisition->team_id]);

    $response = ScreeningResponse::factory()->yesNoResponse(true)->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('clears a stale is_knockout_fail when a re-evaluation now passes', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    $question = ScreeningQuestion::factory()->yesNo()->knockout(['expected' => 'yes'])
        ->for($this->requisition, 'screenable')
        ->create(['team_id' => $this->requisition->team_id]);

    // The response previously failed and was persisted as a knockout fail, but now passes.
    $response = ScreeningResponse::factory()->knockoutFailed()->yesNoResponse(true)->create([
        'team_id' => $this->requisition->team_id,
        'application_id' => $this->application->id,
        'question_id' => $question->id,
    ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    // The answer now passes, so the stale failure flag must be cleared.
    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('reports hadKnockoutCriteria false when there are no knockout questions', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    ScreeningQuestion::factory()->text()
        ->for($this->requisition, 'screenable')
        ->create([
            'team_id' => $this->requisition->team_id,
            'is_knockout' => false,
            'knockout_criteria' => null,
        ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria === false);
});

it('treats a missing answer to a knockout question as a pass', function (): void {
    Event::fake([ScreeningEvaluated::class]);
    ScreeningQuestion::factory()->yesNo()->knockout(['expected' => 'yes'])
        ->for($this->requisition, 'screenable')
        ->create(['team_id' => $this->requisition->team_id]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false && $e->hadKnockoutCriteria);
});

it('does not knock out a candidate when accepted criteria references options that no longer exist (EC-2)', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    // Recruiter set accepted=['Senior'] then renamed the option to 'jr'.
    $question = ScreeningQuestion::factory()->knockout(['accepted' => ['Senior']]) // stale
        ->for($this->requisition, 'screenable')
        ->create([
            'team_id' => $this->requisition->team_id,
            'question_type' => QuestionTypeEnum::SingleChoice,
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['value' => 'jr', 'label' => 'Junior'],
                ],
            ],
            'is_required' => true,
        ]);

    $response = ScreeningResponse::factory()->singleChoiceResponse('jr') // valid current choice
        ->create([
            'team_id' => $this->requisition->team_id,
            'application_id' => $this->application->id,
            'question_id' => $question->id,
        ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    // Stale criteria must NOT reject the candidate.
    expect($response->refresh()->is_knockout_fail)->toBeFalse();

    Event::assertDispatched(fn (ScreeningEvaluated $e): bool => $e->anyKnockoutFailed === false);
});

it('still evaluates against the accepted options that remain valid (EC-2 partial)', function (): void {
    Event::fake([ScreeningEvaluated::class]);

    $question = ScreeningQuestion::factory()->knockout(['accepted' => ['a', 'gone']]) // 'gone' stale, 'a' valid
        ->for($this->requisition, 'screenable')
        ->create([
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
        ]);

    $response = ScreeningResponse::factory()->singleChoiceResponse('b') // not in remaining valid accepted ['a'] → fails
        ->create([
            'team_id' => $this->requisition->team_id,
            'application_id' => $this->application->id,
            'question_id' => $question->id,
        ]);

    resolve(EvaluateScreeningResponses::class)->execute($this->application);

    expect($response->refresh()->is_knockout_fail)->toBeTrue();
});
