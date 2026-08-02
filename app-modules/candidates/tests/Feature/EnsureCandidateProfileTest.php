<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

it('creates a candidate profile with the onboarding defaults', function (): void {
    $user = User::factory()->create();

    // Enquanto o UserObserver ainda criar o perfil (até a Tarefa 5), o registro precisa
    // sair da frente para que este teste exercite o caminho de criação da Action.
    Candidate::query()->where('user_id', $user->getKey())->forceDelete();

    $candidate = resolve(EnsureCandidateProfile::class)->execute($user);

    expect($candidate->user_id)->toBe($user->getKey())
        ->and($candidate->is_onboarded)->toBeFalse()
        ->and($candidate->preferred_language)->toBe('en')
        ->and($candidate->expected_salary_currency)->toBe('USD')
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
