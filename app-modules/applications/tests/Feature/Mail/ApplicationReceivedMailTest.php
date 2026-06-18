<?php

declare(strict_types=1);

use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobPosting;

it('builds the application received mail with subject and job title', function (): void {
    $application = Application::factory()->create();
    JobPosting::factory()->for($application->requisition, 'jobRequisition')->create();
    $application->load('requisition.post');

    $jobTitle = $application->requisition->post->title;

    $mailable = new ApplicationReceivedMail($application);

    $mailable->assertHasSubject(__('applications::filament.emails.application_received.subject', ['job' => $jobTitle]));
    $mailable->assertSeeInHtml($jobTitle);
});
