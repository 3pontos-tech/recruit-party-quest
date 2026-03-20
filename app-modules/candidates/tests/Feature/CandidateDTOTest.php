<?php

declare(strict_types=1);
use He4rt\Candidates\DTOs\CandidateEducationDTO;
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
});
