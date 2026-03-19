<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Widgets\ApplicationsPerDayWidget;
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
    livewire(ApplicationsPerDayWidget::class)->assertOk();
});

it('has a default filter of 30 days', function (): void {
    livewire(ApplicationsPerDayWidget::class)
        ->assertSet('filter', '30');
});

it('caches query results with a team-scoped key', function (): void {
    $teamId = $this->recruiter->team->getKey();

    livewire(ApplicationsPerDayWidget::class)->assertOk();

    expect(Cache::has(sprintf('widget.applications_per_day.%s.30', $teamId)))->toBeTrue();
});

it('uses a separate cache key per filter value', function (): void {
    $teamId = $this->recruiter->team->getKey();

    livewire(ApplicationsPerDayWidget::class)
        ->set('filter', '7')
        ->assertOk();

    expect(Cache::has(sprintf('widget.applications_per_day.%s.7', $teamId)))->toBeTrue();
});

it('only counts applications from the current team', function (): void {
    $teamId = $this->recruiter->team->getKey();

    Application::factory()
        ->for($this->recruiter->team, 'team')
        ->create(['created_at' => now()]);

    // Applications for a different team should be excluded
    Application::factory(3)->create(['created_at' => now()]);

    Cache::forget(sprintf('widget.applications_per_day.%s.30', $teamId));

    livewire(ApplicationsPerDayWidget::class)->assertOk();

    $counts = Cache::get(sprintf('widget.applications_per_day.%s.30', $teamId));
    expect($counts->sum())->toBe(1);
});

it('filter range excludes applications older than the selected period', function (): void {
    $teamId = $this->recruiter->team->getKey();

    // Application outside the 7-day range
    Application::factory()
        ->for($this->recruiter->team, 'team')
        ->create(['created_at' => now()->subDays(10)]);

    // Application inside the 7-day range
    Application::factory()
        ->for($this->recruiter->team, 'team')
        ->create(['created_at' => now()]);

    livewire(ApplicationsPerDayWidget::class)
        ->set('filter', '7')
        ->assertOk();

    $counts = Cache::get(sprintf('widget.applications_per_day.%s.7', $teamId));
    expect($counts->sum())->toBe(1);
});
