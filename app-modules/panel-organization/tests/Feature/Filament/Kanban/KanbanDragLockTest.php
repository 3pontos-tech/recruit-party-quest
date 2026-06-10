<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\KanbanStages;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::Admin->value);
    actingAs($this->admin);

    $this->team = Team::factory()->create(['owner_id' => $this->admin->id]);
    filament()->setTenant($this->team);
});

it('keeps the Kanban read-only: dragging a card does not move the stage', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::InProgress)
        ->create(['team_id' => $this->team->id]);

    $original = $application->current_stage_id;
    $target = $application->requisition->stages()
        ->where('stage_type', StageTypeEnum::Hired)->firstOrFail();

    expect($target->id)->not->toBe($original);

    $component = new KanbanStages();
    $component->requisitionId = $application->requisition_id;
    $component->moveCard($application->id, $target->id);

    expect($application->fresh()->current_stage_id)->toBe($original);
});
