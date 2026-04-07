<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
});

it('can render edit job requisition page', function (): void {
    $requisition = JobRequisition::factory()->create();

    livewire(EditJobRequisition::class, ['record' => $requisition->getRouteKey()])
        ->assertOk();
});

it('edit form is pre-populated with correct data', function (): void {
    $requisition = JobRequisition::factory()->create([
        'status' => RequisitionStatusEnum::Draft,
        'work_arrangement' => WorkArrangementEnum::Remote,
        'employment_type' => EmploymentTypeEnum::FullTimeEmployee,
    ]);

    livewire(EditJobRequisition::class, ['record' => $requisition->getRouteKey()])
        ->assertFormSet([
            'status' => RequisitionStatusEnum::Draft,
            'work_arrangement' => WorkArrangementEnum::Remote,
            'employment_type' => EmploymentTypeEnum::FullTimeEmployee,
        ]);
});
