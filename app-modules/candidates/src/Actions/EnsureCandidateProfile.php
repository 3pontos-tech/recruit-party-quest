<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions;

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

/**
 * Materializa o perfil de candidato de um usuário.
 *
 * Idempotente: devolve o perfil existente quando já houver um. Os defaults repetem os
 * `default()` das colunas em `create_candidates_table`, mantendo a intenção legível sem
 * depender do schema.
 */
final class EnsureCandidateProfile
{
    public function execute(User $user): Candidate
    {
        return Candidate::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            [
                'is_onboarded' => false,
                'preferred_language' => 'en',
                'expected_salary_currency' => 'USD',
                'is_open_to_remote' => true,
            ],
        );
    }
}
