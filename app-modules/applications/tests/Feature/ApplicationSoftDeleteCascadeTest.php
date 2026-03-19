<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

use function Pest\Laravel\assertSoftDeleted;

it('soft deletes applications when job requisition is deleted', function (): void {
    $requisition = JobRequisition::factory()->create();
    $applications = Application::factory()->count(3)->create(['requisition_id' => $requisition->id]);

    $requisition->delete();

    foreach ($applications as $application) {
        assertSoftDeleted(Application::class, ['id' => $application->id]);
    }
});

it('restores applications when job requisition is restored', function (): void {
    $requisition = JobRequisition::factory()->create();
    $application = Application::factory()->create(['requisition_id' => $requisition->id]);

    $requisition->delete();
    assertSoftDeleted(Application::class, ['id' => $application->id]);

    $requisition->restore();

    expect(Application::query()->find($application->id))->not->toBeNull();
});

it('does not affect other requisitions applications when one is deleted', function (): void {
    $requisitionA = JobRequisition::factory()->create();
    $requisitionB = JobRequisition::factory()->create();
    $appA = Application::factory()->create(['requisition_id' => $requisitionA->id]);
    $appB = Application::factory()->create(['requisition_id' => $requisitionB->id]);

    $requisitionA->delete();

    assertSoftDeleted(Application::class, ['id' => $appA->id]);
    expect(Application::query()->find($appB->id))->not->toBeNull();
});

it('excludes soft deleted applications from normal queries', function (): void {
    $requisition = JobRequisition::factory()->create();
    Application::factory()->count(2)->create(['requisition_id' => $requisition->id]);

    $requisition->delete();

    expect(Application::query()->where('requisition_id', $requisition->id)->count())->toBe(0);
    expect(Application::withTrashed()->where('requisition_id', $requisition->id)->count())->toBe(2);
});
