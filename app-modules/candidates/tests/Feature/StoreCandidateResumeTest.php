<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\Onboarding\StoreCandidateResume;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->candidate = candidateFor(User::factory()->create());
});

it('stores work experiences and education in a single call', function (): void {
    resolve(StoreCandidateResume::class)->execute($this->candidate, CandidateOnboardingDTO::make([
        'work_experiences' => [
            [
                'company_name' => 'Nubank',
                'position' => 'Analista de RH',
                'description' => 'Recrutamento e seleção',
                'start_date' => '2023-03-01',
            ],
        ],
        'education' => [
            [
                'institution' => 'MIT',
                'degree' => 'Bachelor',
                'field_of_study' => 'Computer Science',
            ],
        ],
    ]));

    assertDatabaseHas(WorkExperience::class, [
        'candidate_id' => $this->candidate->getKey(),
        'company_name' => 'Nubank',
    ]);

    assertDatabaseHas(Education::class, [
        'candidate_id' => $this->candidate->getKey(),
        'institution' => 'MIT',
    ]);
});

it('does nothing when the payload has no resume keys', function (): void {
    resolve(StoreCandidateResume::class)->execute(
        $this->candidate,
        CandidateOnboardingDTO::make(['headline' => 'Dev']),
    );

    assertDatabaseCount(WorkExperience::class, 0);
    assertDatabaseCount(Education::class, 0);
});

it('writes to the given profile, not to the authenticated one', function (): void {
    $other = candidateFor(User::factory()->create());

    resolve(StoreCandidateResume::class)->execute($other, CandidateOnboardingDTO::make([
        'work_experiences' => [
            [
                'company_name' => 'Stone',
                'start_date' => '2022-01-01',
            ],
        ],
    ]));

    assertDatabaseCount(WorkExperience::class, 1);
    assertDatabaseHas(WorkExperience::class, [
        'candidate_id' => $other->getKey(),
        'company_name' => 'Stone',
    ]);
});
