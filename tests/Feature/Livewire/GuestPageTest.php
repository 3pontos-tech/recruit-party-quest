<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\JobRequisitions\Pages\ViewJobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobPosting;

it('should render', function (): void {
    $jobPost = JobPosting::factory()->create();
    $jobRequisition = $jobPost->jobRequisition;

    $this->from('/')
        ->get(route(ViewJobRequisition::getRouteName(), ['record' => $jobPost->slug]))
        ->assertOk()
        ->assertSeeText($jobPost->title)
        ->assertSeeText($jobRequisition->team->name);
});
