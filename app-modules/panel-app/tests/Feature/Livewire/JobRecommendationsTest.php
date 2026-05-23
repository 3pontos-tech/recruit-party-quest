<?php

declare(strict_types=1);

use He4rt\App\Livewire\JobRecommendations;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

use function Pest\Livewire\livewire;

it('renders all published public jobs when no filter is applied', function (): void {
    $jobs = JobRequisition::factory(3)->available()->create();

    $draft = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Draft]);

    $livewire = livewire(JobRecommendations::class)->assertOk();

    $jobs->each(fn (JobRequisition $job) => $livewire->assertSee($job->getKey()));

    $livewire->assertDontSee($draft->getKey());
});

it('filters jobs by employment type when jobTypes is an array of strings', function (): void {
    $fullTime = JobRequisition::factory()->available()->create([
        'employment_type' => EmploymentTypeEnum::Clt,
    ]);

    $partTime = JobRequisition::factory()->available()->create([
        'employment_type' => EmploymentTypeEnum::Contractor,
    ]);

    livewire(JobRecommendations::class)
        ->set('jobTypes', [EmploymentTypeEnum::Clt->value])
        ->assertOk()
        ->assertSee($fullTime->getKey())
        ->assertDontSee($partTime->getKey());
});

it('filters jobs when jobTypes contains EnumSynth nested array format', function (): void {
    $fullTime = JobRequisition::factory()->available()->create([
        'employment_type' => EmploymentTypeEnum::Clt,
    ]);

    $partTime = JobRequisition::factory()->available()->create([
        'employment_type' => EmploymentTypeEnum::Contractor,
    ]);

    // Simula o formato que o EnumSynth do Livewire produz ao desserializar o snapshot
    livewire(JobRecommendations::class)
        ->set('jobTypes', [
            ['value' => EmploymentTypeEnum::Clt->value, 'class' => EmploymentTypeEnum::class],
        ])
        ->assertOk()
        ->assertSee($fullTime->getKey())
        ->assertDontSee($partTime->getKey());
});

it('returns no jobs when jobTypes filter matches no employment type in the database', function (): void {
    $job = JobRequisition::factory()->available()->create([
        'employment_type' => EmploymentTypeEnum::Clt,
    ]);

    livewire(JobRecommendations::class)
        ->set('jobTypes', ['non_existent_type'])
        ->assertOk()
        ->assertDontSee((string) $job->getKey());
});
