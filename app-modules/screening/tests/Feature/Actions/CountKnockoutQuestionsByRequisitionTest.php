<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\CountKnockoutQuestionsByRequisition;
use He4rt\Screening\Models\ScreeningQuestion;

it('counts knockout questions attached to the requisition and to its stages', function (): void {
    $withQuestions = JobRequisition::factory()->create();
    $team = $withQuestions->team;
    $stage = $withQuestions->stages()->orderBy('display_order')->first();
    $without = JobRequisition::factory()->recycle($team)->create();

    ScreeningQuestion::factory()->yesNo()->knockout()->create(['team_id' => $team->getKey(), 'screenable_type' => $withQuestions->getMorphClass(), 'screenable_id' => $withQuestions->getKey()]);
    ScreeningQuestion::factory()->yesNo()->knockout()->create(['team_id' => $team->getKey(), 'screenable_type' => $stage->getMorphClass(), 'screenable_id' => $stage->getKey()]);
    ScreeningQuestion::factory()->yesNo()->create(['team_id' => $team->getKey(), 'screenable_type' => $withQuestions->getMorphClass(), 'screenable_id' => $withQuestions->getKey(), 'is_knockout' => false, 'knockout_criteria' => null]);

    $requisitions = JobRequisition::query()->with('stages')->whereKey([$withQuestions->getKey(), $without->getKey()])->get();

    $counts = resolve(CountKnockoutQuestionsByRequisition::class)->execute($requisitions);

    expect($counts[$withQuestions->getKey()])->toBe(2)
        ->and($counts)->not->toHaveKey($without->getKey());
});
