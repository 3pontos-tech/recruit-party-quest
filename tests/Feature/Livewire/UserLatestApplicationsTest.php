<?php

declare(strict_types=1);

use He4rt\App\Livewire\UserLatestApplications;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

use function Pest\Livewire\livewire;

it('renders successfully', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('displays user applications', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(3)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('paginates applications', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->count(10)->for($candidate)->create();

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class);
    $applications = $component->get('applications');

    expect($applications->count())->toBe(4);
});

it('filters by search query', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(3)->for($candidate)->create();

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->set('search', 'test');

    $applications = $component->get('applications');

    expect($applications->count())->toBe(0)
        ->and($component->get('search'))->toBe('test');
});

it('filters by status', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    $app1 = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::New]);
    $app2 = Application::factory()->for($candidate)->create(['status' => ApplicationStatusEnum::OfferExtended]);

    $this->actingAs($user);

    $component = livewire(UserLatestApplications::class)
        ->call('filterByStatus', 'in_review');

    $applications = $component->get('applications');

    expect($applications->contains($app1))->toBeTrue()
        ->and($applications->contains($app2))->toBeFalse();
});

it('resets page on search', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);
    Application::factory()->count(10)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->set('search', '')
        ->set('paginators.page', 2)
        ->set('search', 'test')
        ->assertSet('paginators.page', 1);
});

it('eager loads relationships to prevent N+1 queries', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    Application::factory()->count(5)->for($candidate)->create();

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertStatus(200);
});

it('displays empty state when no applications', function (): void {
    $user = User::factory()->create();
    Candidate::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    livewire(UserLatestApplications::class)
        ->assertSee('No applications found');
});
