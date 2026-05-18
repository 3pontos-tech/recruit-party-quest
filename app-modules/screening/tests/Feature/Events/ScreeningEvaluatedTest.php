<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Screening\Events\ScreeningEvaluated;

it('carries the application and the evaluation flags', function (): void {
    $application = Application::factory()->create();

    $event = new ScreeningEvaluated($application, anyKnockoutFailed: true, hadKnockoutCriteria: true);

    expect($event->application->is($application))->toBeTrue()
        ->and($event->anyKnockoutFailed)->toBeTrue()
        ->and($event->hadKnockoutCriteria)->toBeTrue();
});
