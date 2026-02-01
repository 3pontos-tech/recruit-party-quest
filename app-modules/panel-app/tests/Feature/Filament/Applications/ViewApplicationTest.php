<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

/** @var User $user */
/** @var Candidate $candidate */
/** @var Application $application */
beforeEach(function (): void {
    // Criar user primeiro, depois candidate associado
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();

    // Refresh para garantir que as relações sejam carregadas
    $this->user->refresh();
    $this->candidate->refresh();

    actingAs($this->user);

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user permission to view applications
    $this->user->givePermissionTo('view_applications');

    // Create application for this candidate
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
});

it('should render the view application page successfully', function (): void {
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

it('should display application details and pipeline information', function (): void {
    /** @var Application $application */
    $application = $this->application;

    $livewire = livewire(ViewApplication::class, ['record' => $application->getKey()])
        ->assertOk()
        ->assertSee('Application Status')
        ->assertSee('You are currently at this stage')
        ->assertSee('Progresso');

    // Test pipeline stages if they exist
    $stages = $application->getPipelineStages();
    $stages->each(function ($stage) use ($livewire): void {
        $livewire->assertSee($stage->name);
        $livewire->assertSee($stage->description);
    });
});

it('should pass correct view data to template', function (): void {
    /** @var Application $application */
    $application = $this->application;

    $livewireComponent = livewire(ViewApplication::class, ['record' => $application->getKey()]);

    // Use reflection to access protected getViewData method
    $reflection = new ReflectionClass($livewireComponent->instance());
    $getViewDataMethod = $reflection->getMethod('getViewData');

    $viewData = $getViewDataMethod->invoke($livewireComponent->instance());

    expect($viewData)
        ->toHaveKey('jobRequisition')
        ->and($viewData['jobRequisition']->id)
        ->toBe($application->requisition->id)
        ->and($viewData)
        ->toHaveKey('currentStage')
        ->and($viewData['currentStage']->id)
        ->toBe($application->currentStage->id)
        ->and($viewData)
        ->toHaveKey('stages');
});

it('should use custom view template', function (): void {
    /** @var Application $application */
    $application = $this->application;

    $livewireComponent = livewire(ViewApplication::class, ['record' => $application->getKey()]);

    // Check that the component has the expected view property via reflection
    $reflection = new ReflectionClass($livewireComponent->instance());
    $viewProperty = $reflection->getProperty('view');

    expect($viewProperty->getValue($livewireComponent->instance()))
        ->toBe('panel-app::filament.applications.view-application');
});

it('should not show breadcrumbs', function (): void {
    /** @var Application $application */
    $application = $this->application;

    $livewireComponent = livewire(ViewApplication::class, ['record' => $application->getKey()]);

    expect($livewireComponent->instance()->getBreadcrumbs())
        ->toBeEmpty();
});

it('should have empty heading and subheading', function (): void {
    /** @var Application $application */
    $application = $this->application;

    $livewireComponent = livewire(ViewApplication::class, ['record' => $application->getKey()]);

    expect($livewireComponent->instance()->getHeading())
        ->toBe('')
        ->and($livewireComponent->instance()->getSubheading())
        ->toBeNull();
});

test('only authorized user can view the application', function (): void {
    // Create a different user without permissions
    /** @var User $unauthorizedUser */
    $unauthorizedUser = User::factory()->create();

    actingAs($unauthorizedUser);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertForbidden();
});

test('user without view_applications permission cannot access', function (): void {
    // Create a user but don't give them the permission
    /** @var User $userWithoutPermission */
    $userWithoutPermission = User::factory()->create();
    Candidate::factory()->for($userWithoutPermission, 'user')->create();

    actingAs($userWithoutPermission);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertForbidden();
});
