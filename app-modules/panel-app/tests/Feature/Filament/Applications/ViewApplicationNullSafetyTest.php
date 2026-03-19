<?php

declare(strict_types=1);

use He4rt\App\Filament\Resources\Applications\Pages\ViewApplication;
use He4rt\App\Livewire\UserLatestApplications;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user);
    $this->candidate->user->givePermissionTo('view_applications');

    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
});

it('renders ViewApplication without error when requisition has no stages', function (): void {
    $this->application->requisition->stages()->delete();

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

it('hides pipeline sidebar when there are no active stages', function (): void {
    $this->application->requisition->stages()->delete();

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertDontSee(__('panel-organization::view.pipeline.title'));
});

it('UserLatestApplications renders without error when application has no stages', function (): void {
    $this->application->requisition->stages()->delete();

    livewire(UserLatestApplications::class)
        ->assertOk();
});

it('UserLatestApplications renders without error when requisition is soft deleted', function (): void {
    $this->application->requisition->delete();

    livewire(UserLatestApplications::class)
        ->assertOk();
});
