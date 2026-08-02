<?php

declare(strict_types=1);
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use Illuminate\Support\Facades\Date;

describe('CandidateEducationDTO', function (): void {
    it('serializes start_date correctly when defined', function (): void {
        $startDate = Date::parse('2024-01-15');
        $dto = new CandidateEducationDTO(
            institution: 'MIT',
            degree: 'Bachelor',
            fieldOfStudy: 'Computer Science',
            isEnrolled: false,
            startDate: $startDate,
        );

        $serialized = $dto->jsonSerialize();

        expect($serialized['start_date'])->toBe('2024-01-15');
    });

    it('uses today() as fallback when start_date is null', function (): void {
        $dto = new CandidateEducationDTO(
            institution: 'FATEC',
            degree: 'Bacharelado',
            fieldOfStudy: 'ADS',
            isEnrolled: true,
        );

        $serialized = $dto->jsonSerialize();
        $todayFormatted = now()->format('Y-m-d');

        expect($serialized['start_date'])->toBe($todayFormatted);
    });

    it('serializes end_date as null when not defined', function (): void {
        $dto = new CandidateEducationDTO(
            institution: 'FATEC',
            degree: 'Bacharelado',
            fieldOfStudy: 'ADS',
            isEnrolled: true,
            startDate: Date::parse('2024-01-01'),
        );

        $serialized = $dto->jsonSerialize();

        expect($serialized['end_date'])->toBeNull();
    });

    it('does not throw when the model omits institution or degree', function (): void {
        $dto = CandidateEducationDTO::make([
            'field_of_study' => 'Psicologia',
            'start_date' => '2018-01-01',
        ]);

        expect($dto->institution)->toBe('')
            ->and($dto->degree)->toBe('')
            ->and($dto->isEnrolled)->toBeFalse();
    });
});

describe('CandidateWorkExperienceDTO', function (): void {
    it('serializes start_date correctly when defined', function (): void {
        $startDate = Date::parse('2023-06-15');
        $dto = new CandidateWorkExperienceDTO(
            companyName: '3-Pontos',
            description: 'PHP Developer',
            isCurrentlyWorking: true,
            startDate: $startDate,
        );

        $serialized = $dto->jsonSerialize();

        expect($serialized['start_date'])->toBe('2023-06-15');
    });

    it('uses today() as fallback when start_date is null', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Google',
            description: 'Software Engineer',
            isCurrentlyWorking: false,
        );

        $serialized = $dto->jsonSerialize();
        $todayFormatted = now()->format('Y-m-d');

        expect($serialized['start_date'])->toBe($todayFormatted);
    });

    it('serializes end_date as null when not defined', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Microsoft',
            description: 'Senior Developer',
            isCurrentlyWorking: true,
            startDate: Date::parse('2020-01-01'),
        );

        $serialized = $dto->jsonSerialize();

        expect($serialized['end_date'])->toBeNull();
    });

    it('does not throw when the model omits company_name', function (): void {
        $dto = CandidateWorkExperienceDTO::make([
            'description' => 'Recrutamento e seleção',
            'start_date' => '2023-03-01',
            'is_currently_working_here' => true,
        ]);

        expect($dto->companyName)->toBe('')
            ->and($dto->description)->toBe('Recrutamento e seleção');
    });

    it('does not throw when the model omits description', function (): void {
        $dto = CandidateWorkExperienceDTO::make([
            'company_name' => 'Nubank',
            'start_date' => '2023-03-01',
        ]);

        expect($dto->description)->toBe('');
    });

    it('defaults position to null and skills to an empty list', function (): void {
        $dto = CandidateWorkExperienceDTO::make([
            'company_name' => 'Nubank',
            'description' => 'Recrutamento',
        ]);

        expect($dto->position)->toBeNull()
            ->and($dto->skills)->toBe([]);
    });

    it('hydrates position and skills when the model provides them', function (): void {
        $dto = CandidateWorkExperienceDTO::make([
            'company_name' => 'Nubank',
            'description' => 'Recrutamento',
            'position' => 'Analista de RH Pleno',
            'skills' => ['Gupy', 'LinkedIn Recruiter'],
        ]);

        expect($dto->position)->toBe('Analista de RH Pleno')
            ->and($dto->skills)->toBe(['Gupy', 'LinkedIn Recruiter']);
    });

    it('discards empty and non-string entries from skills', function (): void {
        $dto = CandidateWorkExperienceDTO::make([
            'company_name' => 'Nubank',
            'description' => 'Recrutamento',
            'skills' => ['Gupy', '', null, 'Excel'],
        ]);

        expect($dto->skills)->toBe(['Gupy', 'Excel']);
    });

    it('survives a jsonSerialize round-trip with the new fields', function (): void {
        $original = CandidateWorkExperienceDTO::make([
            'company_name' => 'Nubank',
            'description' => 'Recrutamento',
            'position' => 'Analista de RH Pleno',
            'skills' => ['Gupy'],
            'start_date' => '2023-03-01',
            'is_currently_working_here' => true,
        ]);

        $restored = CandidateWorkExperienceDTO::make($original->jsonSerialize());

        expect($restored->position)->toBe('Analista de RH Pleno')
            ->and($restored->skills)->toBe(['Gupy'])
            ->and($restored->companyName)->toBe('Nubank');
    });
});

describe('CandidateOnboardingDTO', function (): void {
    it('hydrates child DTOs from raw arrays in the broadcast payload shape', function (): void {
        $dto = CandidateOnboardingDTO::make([
            'education' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'Bachelor',
                    'field_of_study' => 'Computer Science',
                    'is_enrolled' => false,
                    'start_date' => '2020-01-01',
                    'end_date' => '2024-01-01',
                ],
            ],
            'work_experiences' => [
                [
                    'company_name' => '3-Pontos',
                    'description' => 'PHP Developer',
                    'is_currently_working_here' => true,
                    'start_date' => '2023-06-15',
                    'end_date' => null,
                ],
            ],
        ]);

        expect($dto->education)->toHaveCount(1)
            ->and($dto->education[0])->toBeInstanceOf(CandidateEducationDTO::class)
            ->and($dto->education[0]->jsonSerialize()['institution'])->toBe('MIT')
            ->and($dto->work_experiences)->toHaveCount(1)
            ->and($dto->work_experiences[0])->toBeInstanceOf(CandidateWorkExperienceDTO::class)
            ->and($dto->work_experiences[0]->jsonSerialize()['company_name'])->toBe('3-Pontos');
    });

    it('defaults missing education and work_experiences keys to empty lists', function (): void {
        $dto = CandidateOnboardingDTO::make([]);

        expect($dto->education)->toBeArray()->toBeEmpty()
            ->and($dto->work_experiences)->toBeArray()->toBeEmpty();
    });
});
