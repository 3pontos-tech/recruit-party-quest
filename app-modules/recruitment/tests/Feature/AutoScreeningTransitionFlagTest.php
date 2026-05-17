<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('defaults auto_screening_transition to false', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition->fresh()->auto_screening_transition)->toBeFalse();
});

it('casts auto_screening_transition to boolean', function (): void {
    $requisition = JobRequisition::factory()->create(['auto_screening_transition' => true]);

    expect($requisition->fresh()->auto_screening_transition)->toBeTrue();
});
