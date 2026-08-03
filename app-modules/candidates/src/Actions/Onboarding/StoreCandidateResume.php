<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\Models\Candidate;

/**
 * Grava o histórico profissional e acadêmico vindo do currículo em um único passo.
 *
 * Existe para que os três pontos de entrada — a conclusão do wizard, o retorno da análise
 * de CV no navegador e o listener que persiste a mesma análise no servidor — não repitam a
 * montagem das collections nem a ordem das chamadas. As Actions granulares seguem
 * disponíveis para quem precisar de apenas uma delas.
 *
 * Recebe o DTO, e não o array cru: quem tem o payload de um formulário ou de um broadcast
 * o hidrata com `CandidateOnboardingDTO::make()` antes de chamar.
 */
final readonly class StoreCandidateResume
{
    public function __construct(
        private StoreCandidateWorkExperiences $workExperiences,
        private StoreCandidateEducation $education,
    ) {}

    public function execute(Candidate $candidate, CandidateOnboardingDTO $resume): void
    {
        $this->workExperiences->execute(
            $candidate,
            new CandidateWorkExperienceCollection($resume->work_experiences),
        );

        $this->education->execute(
            $candidate,
            new CandidateEducationCollection($resume->education),
        );
    }
}
