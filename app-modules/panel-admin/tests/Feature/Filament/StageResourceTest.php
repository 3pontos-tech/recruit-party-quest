<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\Pages\EditStage;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\Pages\ListStages;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('cannot access list stages page directly', function (): void {
    livewire(ListStages::class)
        ->assertForbidden();
});

it('can render edit stage page', function (): void {
    $requisition = JobRequisition::factory()->available()->create();
    $stage = $requisition->stages->first();

    livewire(EditStage::class, ['record' => $stage->getRouteKey()])
        ->assertOk();
});

it('validates required fields when editing stage', function (): void {
    $requisition = JobRequisition::factory()->available()->create();
    $stage = $requisition->stages->first();

    livewire(EditStage::class, ['record' => $stage->getRouteKey()])
        ->set('data.name', '')
        ->set('data.stage_type')
        ->call('save')
        ->assertHasFormErrors(['name', 'stage_type']);
});

it('validates expected_duration_days minimum value', function (): void {
    $requisition = JobRequisition::factory()->available()->create();
    $stage = $requisition->stages->first();

    livewire(EditStage::class, ['record' => $stage->getRouteKey()])
        ->set('data.expected_duration_days', 0)
        ->call('save')
        ->assertHasFormErrors(['expected_duration_days']);
});
