<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Widgets\RecruitmentOverviewWidget;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
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
    livewire(RecruitmentOverviewWidget::class)->assertOk();
});

it('shows zero counts when no data', function (): void {
    livewire(RecruitmentOverviewWidget::class)
        ->assertSee('0');
});

it('counts open requisitions correctly', function (): void {
    $teamId = $this->recruiter->team->getKey();

    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Approved]);
    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Approved]);
    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Published]);
    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Closed]);

    Cache::forget('widget.recruitment_overview.'.$teamId);

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    $stats = Cache::get('widget.recruitment_overview.'.$teamId);
    expect($stats['open_requisitions'])->toBe(3);
});

it('counts total applications correctly', function (): void {
    $teamId = $this->recruiter->team->getKey();

    Application::factory(3)->for($this->recruiter->team, 'team')->create();

    Cache::forget('widget.recruitment_overview.'.$teamId);

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    $stats = Cache::get('widget.recruitment_overview.'.$teamId);
    expect($stats['total_applications'])->toBe(3);
});

it('counts offers extended correctly', function (): void {
    $teamId = $this->recruiter->team->getKey();

    Application::factory(2)->for($this->recruiter->team, 'team')->create(['offer_extended_at' => now()]);
    Application::factory()->for($this->recruiter->team, 'team')->create(['offer_extended_at' => null]);

    Cache::forget('widget.recruitment_overview.'.$teamId);

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    $stats = Cache::get('widget.recruitment_overview.'.$teamId);
    expect($stats['offers_extended'])->toBe(2);
});

it('sums positions available correctly', function (): void {
    $teamId = $this->recruiter->team->getKey();

    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Approved, 'positions_available' => 3]);
    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Published, 'positions_available' => 2]);
    JobRequisition::factory()->for($this->recruiter->team, 'team')->create(['status' => RequisitionStatusEnum::Closed, 'positions_available' => 5]);

    Cache::forget('widget.recruitment_overview.'.$teamId);

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    $stats = Cache::get('widget.recruitment_overview.'.$teamId);
    expect($stats['positions_available'])->toBe(5);
});

it('only counts data from the current team', function (): void {
    $teamId = $this->recruiter->team->getKey();

    // Data for a different team — should be excluded
    JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Approved]);
    Application::factory()->create();

    Cache::forget('widget.recruitment_overview.'.$teamId);

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    $stats = Cache::get('widget.recruitment_overview.'.$teamId);
    expect($stats['open_requisitions'])->toBe(0)
        ->and($stats['total_applications'])->toBe(0);
});

it('caches results with a team-scoped key', function (): void {
    $teamId = $this->recruiter->team->getKey();

    livewire(RecruitmentOverviewWidget::class)->assertOk();

    expect(Cache::has('widget.recruitment_overview.'.$teamId))->toBeTrue();
});
