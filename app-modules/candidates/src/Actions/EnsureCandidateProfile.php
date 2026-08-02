<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions;

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;

/**
 * Materializa o perfil de candidato de um usuário.
 *
 * Idempotente: devolve o perfil existente quando já houver um. Os defaults são declarados
 * aqui, e não herdados das colunas: `preferred_language` e `expected_salary_currency`
 * assumem o público brasileiro da plataforma, divergindo de propósito do `default()` do
 * schema (`en` / `USD`). A grafia `pt_BR` é a que `User::preferredLocale()` e o select do
 * onboarding reconhecem.
 */
final class EnsureCandidateProfile
{
    public function execute(User $user): Candidate
    {
        return Candidate::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            [
                'is_onboarded' => false,
                'preferred_language' => 'pt_BR',
                'expected_salary_currency' => 'BRL',
                'is_open_to_remote' => true,
            ],
        );
    }
}
