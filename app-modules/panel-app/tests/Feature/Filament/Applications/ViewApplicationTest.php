<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user);
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    Permission::factory()
        ->create([
            'name' => 'view_applications',
            'guard_name' => 'web',
            'action' => PermissionsEnum::View,
        ]);
    $this->candidate->user->givePermissionTo('view_applications');
});
it('should render', function (): void {
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

test('only authorized user can see the application', function (): void {
    actingAs(User::factory()->create());
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertforbidden();
});
