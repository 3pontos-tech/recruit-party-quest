<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Departments\DepartmentResource;
use He4rt\Admin\Filament\Resources\Departments\Pages\CreateDepartment;
use He4rt\Admin\Filament\Resources\Departments\Pages\EditDepartment;
use He4rt\Admin\Filament\Resources\Departments\Pages\ListDepartments;
use He4rt\Permissions\Roles;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list departments with translated columns', function (): void {
    $team = Team::factory()->create();
    $departments = Department::factory()->count(3)->create(['team_id' => $team->id]);

    expect(__('teams::filament.department.fields.name'))->toBe('Name')
        ->and(__('teams::filament.department.fields.team'))->toBe('Team');

    livewire(ListDepartments::class)
        ->assertCanSeeTableRecords($departments)
        ->assertSee(__('teams::filament.department.fields.name'))
        ->assertSee(__('teams::filament.department.fields.team'));
});

it('can render create department page', function (): void {
    $this->get(DepartmentResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit department page', function (): void {
    $department = Department::factory()->create();

    $this->get(DepartmentResource::getUrl('edit', ['record' => $department]))
        ->assertSuccessful();
});

it('can create a department', function (): void {
    $team = Team::factory()->create();
    $team->members()->attach($team->owner_id);

    livewire(CreateDepartment::class)
        ->fillForm([
            'head_user_id' => $team->owner_id,
            'team_id' => $team->id,
            'name' => 'Engineering',
            'description' => 'Engineering department',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('departments', [
        'team_id' => $team->id,
        'name' => 'Engineering',
    ]);
});

it('can update a department', function (): void {
    $department = Department::factory()->create();

    livewire(EditDepartment::class, ['record' => $department->id])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('departments', [
        'id' => $department->id,
        'name' => 'Updated Name',
    ]);
});
