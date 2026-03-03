<?php

declare(strict_types=1);

use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Team;
use He4rt\Teams\TeamStatus;
use He4rt\Users\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;

test('team factory creates valid team', function (): void {
    $team = Team::factory()->create();
    expect($team)->toBeInstanceOf(Team::class)
        ->and($team->status)->toBe(TeamStatus::Active)
        ->and($team->owner_id)->not->toBeNull()
        ->and($team->name)->not->toBeEmpty();
});

test('team has owner relation', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    expect($team->owner->id)->toBe($owner->id);
});

test('team has members relation', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $team->members()->attach($user);

    expect($team->members->first()->id)->toBe($user->id);
});

test('creating a team auto-creates a recruiter record for the owner', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    assertDatabaseHas('recruiters', [
        'user_id' => $owner->id,
        'team_id' => $team->id,
        'is_active' => true,
    ]);
});

test('creating a team twice for same owner does not duplicate recruiter', function (): void {
    $owner = User::factory()->create();
    Team::factory()->create(['owner_id' => $owner->id]);
    Team::factory()->create(['owner_id' => $owner->id]);

    expect(Recruiter::query()->where('user_id', $owner->id)->count())->toBe(2);
});

test('removing a team member soft-deletes their recruiter record', function (): void {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $team->members()->attach($user);

    assertDatabaseHas('recruiters', ['user_id' => $user->id, 'team_id' => $team->id, 'deleted_at' => null]);

    $team->members()->detach($user);

    assertSoftDeleted('recruiters', ['user_id' => $user->id, 'team_id' => $team->id]);
});

test('changing team owner auto-creates a recruiter record for the new owner', function (): void {
    $originalOwner = User::factory()->create();
    $newOwner = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $originalOwner->id]);

    $team->update(['owner_id' => $newOwner->id]);

    assertDatabaseHas('recruiters', [
        'user_id' => $newOwner->id,
        'team_id' => $team->id,
        'is_active' => true,
    ]);
});
