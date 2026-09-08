<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Team;

beforeEach(function (): void {
    $this->team = Team::factory()->create();
    $this->busy = JobRequisition::factory()->recycle($this->team)->create();
    JobPosting::factory()->for($this->busy, 'jobRequisition')->createOne(['title' => 'Backend Engineer']);
    $this->quiet = JobRequisition::factory()->recycle($this->team)->create();
    JobPosting::factory()->for($this->quiet, 'jobRequisition')->createOne(['title' => 'Product Designer']);

    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->count(2)->create(['status' => ApplicationStatusEnum::New]);
    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->create(['status' => ApplicationStatusEnum::InProgress]);
    Application::factory()->recycle($this->team)->for($this->busy, 'requisition')->create(['status' => ApplicationStatusEnum::Hired]);
    $seen = Application::factory()->recycle($this->team)->for($this->quiet, 'requisition')->create(['status' => ApplicationStatusEnum::New]);
    ApplicationView::factory()->forApplication($seen)->create();
});

it('counts applications per requisition for the recruiter overview', function (): void {
    $busy = JobRequisition::query()->withRecruiterOverviewCounts()->findOrFail($this->busy->getKey());
    $quiet = JobRequisition::query()->withRecruiterOverviewCounts()->findOrFail($this->quiet->getKey());

    expect((int) $busy->getAttribute('applications_count'))->toBe(4)
        ->and((int) $busy->getAttribute('new_applications_count'))->toBe(2)
        ->and((int) $busy->getAttribute('active_applications_count'))->toBe(1)
        ->and((int) $busy->getAttribute('hired_applications_count'))->toBe(1)
        ->and((int) $busy->getAttribute('unseen_applications_count'))->toBe(4)
        ->and($busy->getAttribute('oldest_new_at'))->not->toBeNull()
        ->and((int) $quiet->getAttribute('unseen_applications_count'))->toBe(0);
});

it('orders and searches the overview', function (): void {
    expect(JobRequisition::query()->withRecruiterOverviewCounts()->orderForRecruiterOverview(RequisitionOverviewSort::New)->pluck('id')->first())->toBe($this->busy->getKey())
        ->and(JobRequisition::query()->withRecruiterOverviewCounts()->orderForRecruiterOverview(RequisitionOverviewSort::Title)->pluck('id')->first())->toBe($this->busy->getKey())
        ->and(JobRequisition::query()->searchTitle('designer')->pluck('id')->all())->toBe([$this->quiet->getKey()]);
});

it('has translated labels for the overview sort', function (): void {
    foreach (['en', 'pt_BR'] as $locale) {
        app()->setLocale($locale);

        foreach (RequisitionOverviewSort::cases() as $case) {
            expect($case->getLabel())->not->toContain('::');
        }
    }
});
