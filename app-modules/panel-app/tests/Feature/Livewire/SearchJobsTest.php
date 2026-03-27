<?php

declare(strict_types=1);

use He4rt\App\Livewire\SearchJobs;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('should only render jobs that has stages and are available', function (): void {
    actingAs(User::factory()->createOne());

    $jobs = JobRequisition::factory(2)->available()->create();

    $anotherJobs = JobRequisition::factory(2)->create([
        'status' => RequisitionStatusEnum::Draft,
    ]);

    $livewire = livewire(SearchJobs::class)
        ->assertOk();

    $jobs->each(function (JobRequisition $job) use ($livewire): void {
        $livewire->assertSee($job->team->name);
        $livewire->assertSee($job->post->title);
        $livewire->assertSee($job->post->summary);
        $livewire->assertSee($job->work_arrangement->getLabel());
        $livewire->assertSee($job->employment_type->getLabel());
        $livewire->assertSee($job->getKey());
    });

    $anotherJobs->each(function (JobRequisition $job) use ($livewire): void {
        $livewire->assertDontSee($job->team->name);
        $livewire->assertDontSee($job->post?->title);
        $livewire->assertDontSee($job->post?->summary);
        $livewire->assertDontSee($job->getKey());
    });
});

it('should not render internal only jobs', function (): void {
    actingAs(User::factory()->createOne());

    $publicJob = JobRequisition::factory()->available()->create();

    $internalJob = JobRequisition::factory()->available()->create([
        'is_internal_only' => true,
    ]);

    $livewire = livewire(SearchJobs::class)
        ->assertOk();

    $livewire->assertSee($publicJob->team->name);
    $livewire->assertSee($publicJob->getKey());

    $livewire->assertDontSee($internalJob->team->name);
    $livewire->assertDontSee($internalJob->getKey());
});

it('should render confidential jobs but hide company name', function (): void {
    actingAs(User::factory()->createOne());

    $publicJob = JobRequisition::factory()->available()->create();

    $confidentialJob = JobRequisition::factory()->available()->create([
        'is_confidential' => true,
    ]);

    $livewire = livewire(SearchJobs::class)
        ->assertOk();

    $livewire->assertSee($publicJob->team->name);
    $livewire->assertSee($publicJob->getKey());

    // Vaga confidencial aparece na listagem
    $livewire->assertSee($confidentialJob->getKey());
    // Nome real da empresa NÃO é exibido
    $livewire->assertDontSee($confidentialJob->team->name);
    // Texto genérico é exibido
    $livewire->assertSee(__('panel-app::filament.confidential.company_name'));
});
