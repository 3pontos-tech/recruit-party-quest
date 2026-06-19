<?php

declare(strict_types=1);

use He4rt\Users\User;
use Illuminate\Contracts\Translation\HasLocalePreference;

it('exposes the candidate preferred language as the locale preference', function (): void {
    $user = User::factory()->create();
    $user->candidate()->update(['preferred_language' => 'pt_BR']);

    expect($user->fresh())->toBeInstanceOf(HasLocalePreference::class)
        ->and($user->fresh()->preferredLocale())->toBe('pt_BR');
});

it('has no locale preference when the user has no candidate', function (): void {
    $user = User::factory()->create();
    $user->candidate()->delete();

    expect($user->fresh()->preferredLocale())->toBeNull();
});
