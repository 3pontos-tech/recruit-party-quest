<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->group('unit')
    ->in('Unit', '../app-modules/*/tests/Unit');

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function (): void {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    })
    ->group('feature')
    ->in('Feature', '../app-modules/*/tests/Feature', '../app-modules/*/tests/Features');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something(): void
{
    // ..
}

/**
 * Cria o perfil de candidato do usuário e deixa a relação resolvida na mesma instância.
 *
 * O perfil não nasce mais junto com o `User` — quem o materializa em produção é o
 * onboarding. Nos fixtures, este helper ocupa esse lugar: além de criar o registro,
 * faz o `setRelation` para que `auth()->user()->candidate` responda sem um `refresh()`.
 *
 * @param  array<string, mixed>  $attributes
 */
function candidateFor(User $user, array $attributes = []): Candidate
{
    $candidate = Candidate::factory()->for($user, 'user')->create($attributes);

    $user->setRelation('candidate', $candidate);

    return $candidate;
}
