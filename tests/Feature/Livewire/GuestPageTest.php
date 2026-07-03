<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('should render', function (): void {
    $jobPost = JobPosting::factory()
        ->for(
            JobRequisition::factory()->state([
                'is_confidential' => false,
                'status' => RequisitionStatusEnum::Published,
            ]),
            'jobRequisition'
        )
        ->create();
    $jobRequisition = $jobPost->jobRequisition;

    $this->from('/')
        ->get(route(ViewJobRequisition::getRouteName(), ['record' => $jobPost->slug]))
        ->assertOk()
        ->assertSeeText($jobPost->title)
        ->assertSeeText($jobRequisition->team->name);
});
