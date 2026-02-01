<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\App\Filament\Resources\Applications\Pages\CreateApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

/** @var User $user */
/** @var Candidate $candidate */
beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();

    $this->user->refresh();
    $this->candidate->refresh();

    actingAs($this->user);

    artisan('sync:permissions');

    $this->user->givePermissionTo('create_applications');
});

it('renders the create application page successfully or redirects appropriately', function (): void {
    $response = get(ApplicationResource::getUrl('create'));

    expect($response->getStatusCode())->toBeIn([200, 302]);
});

it('renders the create application page via livewire', function (): void {
    livewire(CreateApplication::class)
        ->assertOk();
});

it('shows create application form elements', function (): void {
    livewire(CreateApplication::class)
        ->assertOk()
        ->assertSee('Create')
        ->assertSeeHtml('form');
});

it('can access the create form without errors', function (): void {
    livewire(CreateApplication::class)
        ->assertOk()
        ->assertHasNoErrors();
});
