<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
});

it('can render list job requisitions page', function (): void {
    livewire(ListJobRequisitions::class)
        ->assertOk();
});

it('can list job requisitions', function (): void {
    $requisitions = JobRequisition::factory()->count(5)->create();

    livewire(ListJobRequisitions::class)
        ->assertCanSeeTableRecords($requisitions);
});
