<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Admin\Filament\Resources\Recruitment\Applications\Pages\EditApplication;
use He4rt\Admin\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
});

it('can list applications', function (): void {
    $applications = Application::factory()->count(3)->create();

    livewire(ListApplications::class)
        ->assertCanSeeTableRecords($applications);
});

it('can render edit application page', function (): void {
    $application = Application::factory()->create();

    livewire(EditApplication::class, ['record' => $application->getRouteKey()])
        ->assertOk();
});

it('can edit application status', function (): void {
    $application = Application::factory()->create([
        'status' => ApplicationStatusEnum::New,
    ]);

    livewire(EditApplication::class, ['record' => $application->getRouteKey()])
        ->assertOk()
        ->set('data.status', ApplicationStatusEnum::InReview->value)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($application->refresh()->status)->toBe(ApplicationStatusEnum::InReview);
});
