<?php

declare(strict_types=1);

use He4rt\Applications\Actions\BuildRequisitionApplicationStats;
use He4rt\Applications\Actions\BuildRequisitionFunnels;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    $this->team = $this->requisition->team;
    $this->stages = $this->requisition->stages()->orderBy('display_order')->get();
    $this->stages[1]->update(['expected_duration_days' => 2]);

    $make = fn (ApplicationStatusEnum $status, int $stageIndex, int $daysAgo): Application => Application::factory()
        ->recycle($this->team)
        ->for($this->requisition, 'requisition')
        ->create(['status' => $status, 'current_stage_id' => $this->stages[$stageIndex]->getKey(), 'created_at' => now()->subDays($daysAgo)]);

    $this->newOne = $make(ApplicationStatusEnum::New, 0, 0);
    $this->lateOne = $make(ApplicationStatusEnum::InProgress, 1, 10);
    $this->closedOne = $make(ApplicationStatusEnum::Rejected, 1, 10);

    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);
    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $this->newOne->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $this->lateOne->getKey(), 'question_id' => $question->getKey()]);
});

it('builds the open funnel per requisition and stage', function (): void {
    $funnels = resolve(BuildRequisitionFunnels::class)->execute([$this->requisition->getKey()]);

    expect($funnels[$this->requisition->getKey()][$this->stages[0]->getKey()])->toBe(1)
        ->and($funnels[$this->requisition->getKey()][$this->stages[1]->getKey()])->toBe(1);
});

it('builds the application stats of a requisition', function (): void {
    $stats = resolve(BuildRequisitionApplicationStats::class)->execute($this->requisition);

    expect($stats->total)->toBe(3)
        ->and($stats->new)->toBe(1)
        ->and($stats->active)->toBe(1)
        ->and($stats->closed)->toBe(1)
        ->and($stats->unseen)->toBe(2)
        ->and($stats->overdue)->toBe(1)
        ->and($stats->knockoutPassed)->toBe(1)
        ->and($stats->knockoutFailed)->toBe(1)
        ->and($stats->knockoutUnanswered)->toBe(1)
        ->and($stats->openTotal())->toBe(2)
        ->and($stats->byStage[$this->stages[1]->getKey()])->toBe(1);
});
