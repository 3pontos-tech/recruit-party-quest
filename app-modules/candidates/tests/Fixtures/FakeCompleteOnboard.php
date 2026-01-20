<?php

declare(strict_types=1);

namespace He4rt\Candidates\Tests\Fixtures;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class FakeCompleteOnboard implements AiAutocompleteInterface
{
    public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
    {
        dd('to no onboarding fake');
        $education = new CandidateEducationDTO(
            institution: 'FATEC',
            degree: 'Bacharelado',
            fieldOfStudy: 'Analise Desenvolvimento de Sistemas',
            isEnrolled: true,
            startDate: '08/01/2024',
            endDate: '08/01/2027',
        );
        $work = new CandidateWorkExperienceDTO(
            companyName: '3-Pontos',
            description: 'working with php, filament, writing some tests',
            isCurrentlyWorking: true,
            startDate: '08/01/2024',
        );

        return CandidateOnboardingDTO::make([
            'education' => $education,
            'work' => $work,
        ]);
    }
}
