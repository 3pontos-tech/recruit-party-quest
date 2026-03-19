<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Widgets\LatestApplicationsWidget;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->recruiter = Recruiter::factory()->createOne();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->recruiter->team);
});

it('renders', function (): void {
    livewire(LatestApplicationsWidget::class)->assertOk();
});

it('shows an empty state when there are no applications', function (): void {
    livewire(LatestApplicationsWidget::class)
        ->assertSee(__('panel-organization::filament.widgets.latest_applications.empty'));
});

it('shows only the latest 6 applications', function (): void {
    Application::factory(8)
        ->for($this->recruiter->team, 'team')
        ->sequence(fn ($seq) => ['created_at' => now()->subMinutes(8 - $seq->index)])
        ->create();

    livewire(LatestApplicationsWidget::class)
        ->assertViewHas('applications', fn ($apps) => $apps->count() === 6);
});

it('only shows applications from the current team', function (): void {
    // Application for a different team
    $otherApp = Application::factory()->create();
    $otherApp->load('candidate.user');

    livewire(LatestApplicationsWidget::class)
        ->assertDontSee($otherApp->candidate->user->name);
});

it('caches applications with a team-scoped key', function (): void {
    $teamId = $this->recruiter->team->getKey();

    livewire(LatestApplicationsWidget::class)->assertOk();

    expect(Cache::has('widget.latest_applications.'.$teamId))->toBeTrue();
});
