<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Livewire\Features\SupportTesting\Testable;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::Admin->value);
    actingAs($this->admin);

    $this->team = Team::factory()->create(['owner_id' => $this->admin->id]);

    filament()->setTenant($this->team);
});

function rejectAction(): TestAction
{
    return TestAction::make('reject_application-action')->schemaComponent('quick-actions');
}

function viewApplicationFor(Application $application): Testable
{
    return livewire(ViewApplication::class, [
        'tenant' => filament()->getTenant(),
        'record' => $application->getKey(),
    ]);
}

it('shows the reject action enabled while the application is still in progress', function (ApplicationStatusEnum $status): void {
    $application = Application::factory()
        ->withStatus($status)
        ->create(['team_id' => $this->team->id]);
    JobPosting::factory()->for($application->requisition)->create();

    viewApplicationFor($application)
        ->assertOk()
        ->assertActionVisible(rejectAction())
        ->assertActionEnabled(rejectAction());
})->with([
    'new' => ApplicationStatusEnum::New,
    'in review' => ApplicationStatusEnum::InReview,
    'in progress' => ApplicationStatusEnum::InProgress,
    'offer extended' => ApplicationStatusEnum::OfferExtended,
]);

it('keeps the reject action visible but disabled once the process reaches a final outcome', function (ApplicationStatusEnum $status): void {
    $application = Application::factory()
        ->withStatus($status)
        ->create(['team_id' => $this->team->id]);
    JobPosting::factory()->for($application->requisition)->create();

    viewApplicationFor($application)
        ->assertOk()
        ->assertActionVisible(rejectAction())
        ->assertActionDisabled(rejectAction());
})->with([
    'rejected' => ApplicationStatusEnum::Rejected,
    'withdrawn' => ApplicationStatusEnum::Withdrawn,
    'offer declined' => ApplicationStatusEnum::OfferDeclined,
    'offer accepted' => ApplicationStatusEnum::OfferAccepted,
    'hired' => ApplicationStatusEnum::Hired,
]);
