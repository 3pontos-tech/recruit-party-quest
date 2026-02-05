<?php

declare(strict_types=1);

use He4rt\App\Livewire\SearchJobs;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('should only render jobs that has stages and are available', function (): void {
    actingAs(User::factory()->createOne());
    $jobs = JobRequisition::factory(2)->available()->create();
    $anotherJobs = JobRequisition::factory(2)->create();
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
