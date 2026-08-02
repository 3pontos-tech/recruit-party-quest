<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\Models\Candidate;

/**
 * Grava o histórico profissional e acadêmico vindo do currículo em um único passo.
 *
 * Existe para que os dois pontos de entrada — a conclusão do wizard e o retorno da
 * análise de CV — não repitam a montagem das collections nem a ordem das chamadas.
 * As Actions granulares seguem disponíveis para quem precisar de apenas uma delas.
 */
final readonly class StoreCandidateResume
{
    public function __construct(
        private StoreCandidateWorkExperiences $workExperiences,
        private StoreCandidateEducation $education,
    ) {}

    /**
     * @param  array<string, mixed>  $fields
     */
    public function execute(Candidate $candidate, array $fields): void
    {
        $this->workExperiences->execute(
            $candidate,
            CandidateWorkExperienceCollection::fromArray($fields['work_experiences'] ?? []),
        );

        $this->education->execute(
            $candidate,
            CandidateEducationCollection::fromArray($fields['education'] ?? []),
        );
    }
}
