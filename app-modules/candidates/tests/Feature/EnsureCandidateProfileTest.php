<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Illuminate\Database\QueryException;

it('creates a candidate profile with the onboarding defaults', function (): void {
    $user = User::factory()->create();

    $candidate = resolve(EnsureCandidateProfile::class)->execute($user);

    expect($candidate->user_id)->toBe($user->getKey())
        ->and($candidate->is_onboarded)->toBeFalse()
        ->and($candidate->preferred_language)->toBe('pt_BR')
        ->and($candidate->expected_salary_currency)->toBe('BRL')
        ->and($candidate->is_open_to_remote)->toBeTrue();
});

it('returns the existing profile instead of creating a second one', function (): void {
    $user = User::factory()->create();
    $action = resolve(EnsureCandidateProfile::class);

    $first = $action->execute($user);
    $second = $action->execute($user);

    expect($second->getKey())->toBe($first->getKey())
        ->and(Candidate::query()->where('user_id', $user->getKey())->count())->toBe(1);
});

it('rejects a second profile for the same user at the database level', function (): void {
    $user = User::factory()->create();
    resolve(EnsureCandidateProfile::class)->execute($user);

    Candidate::query()->create(['user_id' => $user->getKey()]);
})->throws(QueryException::class);

it('allows a new profile after the previous one was soft deleted', function (): void {
    $user = User::factory()->create();
    $first = resolve(EnsureCandidateProfile::class)->execute($user);

    $first->delete();

    $second = Candidate::query()->create(['user_id' => $user->getKey()]);

    expect($second->getKey())->not->toBe($first->getKey());
});
