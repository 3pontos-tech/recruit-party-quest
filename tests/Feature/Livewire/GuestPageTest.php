<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobPosting;

it('should render', function (): void {
    $jobPost = JobPosting::factory()->create();
    $jobRequisition = $jobPost->jobRequisition;

    $this->from('/')
        ->get(route('filament.app.resources.job-requisitions.view', ['record' => $jobRequisition]))
        ->assertOk()
        ->assertSeeText($jobPost->title)
        ->assertSeeText($jobRequisition->team->name);
});
