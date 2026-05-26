<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->createOne();
    $this->team = $this->requisition->team;

    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

function makeAppInRequisition(JobRequisition $requisition, ApplicationStatusEnum $status, string $createdAt): Application
{
    $candidate = Candidate::factory()->create();

    return Application::factory()
        ->for($requisition, 'requisition')
        ->for($candidate, 'candidate')
        ->state([
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])
        ->create();
}

it('renders the navigation bar when the requisition has multiple active applications', function (): void {
    $apps = collect([
        '2026-05-01 10:00:00',
        '2026-05-02 10:00:00',
        '2026-05-03 10:00:00',
        '2026-05-04 10:00:00',
        '2026-05-05 10:00:00',
    ])->map(fn (string $ts) => makeAppInRequisition(
        $this->requisition,
        ApplicationStatusEnum::InReview,
        $ts,
    ));

    livewire(ViewApplication::class, ['record' => $apps[2]->getKey()])
        ->assertOk()
        ->assertSee(__('panel-organization::view.navigation.previous'))
        ->assertSee(__('panel-organization::view.navigation.next'))
        ->assertSee('3')
        ->assertSee('5')
        ->assertSee(ApplicationResource::getUrl('view', ['record' => $apps[1]]), escape: false)
        ->assertSee(ApplicationResource::getUrl('view', ['record' => $apps[3]]), escape: false);
});

it('hides the navigation bar when the requisition has only one active application', function (): void {
    $only = makeAppInRequisition($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');

    livewire(ViewApplication::class, ['record' => $only->getKey()])
        ->assertOk()
        ->assertDontSee(__('panel-organization::view.navigation.aria_label'));
});

it('hides the navigation bar when the current application is rejected', function (): void {
    foreach (range(1, 10) as $i) {
        makeAppInRequisition($this->requisition, ApplicationStatusEnum::InReview, sprintf('2026-05-%02d 10:00:00', $i));
    }

    $rejected = makeAppInRequisition($this->requisition, ApplicationStatusEnum::Rejected, '2026-05-11 10:00:00');

    livewire(ViewApplication::class, ['record' => $rejected->getKey()])
        ->assertOk()
        ->assertDontSee(__('panel-organization::view.navigation.aria_label'));
});

it('hides the navigation bar when the current application is withdrawn', function (): void {
    foreach (range(1, 3) as $i) {
        makeAppInRequisition($this->requisition, ApplicationStatusEnum::InReview, sprintf('2026-05-%02d 10:00:00', $i));
    }

    $withdrawn = makeAppInRequisition($this->requisition, ApplicationStatusEnum::Withdrawn, '2026-05-11 10:00:00');

    livewire(ViewApplication::class, ['record' => $withdrawn->getKey()])
        ->assertOk()
        ->assertDontSee(__('panel-organization::view.navigation.aria_label'));
});

it('disables the previous button on the first application', function (): void {
    $apps = collect([
        '2026-05-01 10:00:00',
        '2026-05-02 10:00:00',
        '2026-05-03 10:00:00',
    ])->map(fn (string $ts) => makeAppInRequisition(
        $this->requisition,
        ApplicationStatusEnum::InReview,
        $ts,
    ));

    livewire(ViewApplication::class, ['record' => $apps[0]->getKey()])
        ->assertOk()
        ->assertSeeHtmlInOrder([
            '<button',
            'disabled',
            __('panel-organization::view.navigation.previous'),
        ]);
});

it('disables the next button on the last application', function (): void {
    $apps = collect([
        '2026-05-01 10:00:00',
        '2026-05-02 10:00:00',
        '2026-05-03 10:00:00',
    ])->map(fn (string $ts) => makeAppInRequisition(
        $this->requisition,
        ApplicationStatusEnum::InReview,
        $ts,
    ));

    livewire(ViewApplication::class, ['record' => $apps[2]->getKey()])
        ->assertOk()
        ->assertSeeHtmlInOrder([
            '<button',
            'disabled',
            __('panel-organization::view.navigation.next'),
        ]);
});

it('lists all active applications in the dropdown excluding rejected/withdrawn', function (): void {
    $a = makeAppInRequisition($this->requisition, ApplicationStatusEnum::InReview, '2026-05-01 10:00:00');
    $b = makeAppInRequisition($this->requisition, ApplicationStatusEnum::Rejected, '2026-05-02 10:00:00');
    $c = makeAppInRequisition($this->requisition, ApplicationStatusEnum::InReview, '2026-05-03 10:00:00');

    livewire(ViewApplication::class, ['record' => $a->getKey()])
        ->assertOk()
        ->assertSee($a->candidate->user->name)
        ->assertSee($c->candidate->user->name)
        ->assertDontSee($b->candidate->user->name);
});
