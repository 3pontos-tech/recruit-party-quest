<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\Onboarding\StoreCandidateEducation;
use He4rt\Candidates\Actions\Onboarding\StoreCandidateWorkExperiences;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();
    $this->user->refresh();
    actingAs($this->user);
});

describe('StoreCandidateWorkExperiences', function (): void {
    it('creates a new work experience', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: '3-Pontos',
            description: 'PHP Developer',
            isCurrentlyWorking: true,
            startDate: Date::parse('2024-01-01'),
        );

        $collection = new CandidateWorkExperienceCollection([$dto]);

        resolve(StoreCandidateWorkExperiences::class)->execute($collection);

        assertDatabaseCount(WorkExperience::class, 1);
        assertDatabaseHas(WorkExperience::class, [
            'company_name' => '3-Pontos',
            'description' => 'PHP Developer',
            'is_currently_working_here' => 1,
        ]);

        $record = WorkExperience::query()->first();
        expect($record->start_date->format('Y-m-d'))->toBe('2024-01-01');
    });

    it('does not create duplicate when company_name and start_date match', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Google',
            description: 'Software Engineer',
            isCurrentlyWorking: false,
            startDate: Date::parse('2020-06-15'),
        );

        $collection = new CandidateWorkExperienceCollection([$dto]);

        // First call
        resolve(StoreCandidateWorkExperiences::class)->execute($collection);
        assertDatabaseCount(WorkExperience::class, 1);

        // Second call with same data
        resolve(StoreCandidateWorkExperiences::class)->execute($collection);
        assertDatabaseCount(WorkExperience::class, 1);
    });

    it('creates a new record when company_name is different', function (): void {
        $dto1 = new CandidateWorkExperienceDTO(
            companyName: 'Company A',
            description: 'Role A',
            isCurrentlyWorking: false,
            startDate: Date::parse('2020-01-01'),
        );

        $dto2 = new CandidateWorkExperienceDTO(
            companyName: 'Company B',
            description: 'Role B',
            isCurrentlyWorking: false,
            startDate: Date::parse('2020-01-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto1])
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto2])
        );

        assertDatabaseCount(WorkExperience::class, 2);
    });

    it('creates a new record when start_date is different', function (): void {
        $dto1 = new CandidateWorkExperienceDTO(
            companyName: 'Acme Corp',
            description: 'Developer',
            isCurrentlyWorking: false,
            startDate: Date::parse('2020-01-01'),
        );

        $dto2 = new CandidateWorkExperienceDTO(
            companyName: 'Acme Corp',
            description: 'Developer',
            isCurrentlyWorking: false,
            startDate: Date::parse('2021-01-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto1])
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto2])
        );

        assertDatabaseCount(WorkExperience::class, 2);
    });

    it('persists position and skills from the extracted dto', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Nubank',
            description: 'Recrutamento e seleção',
            isCurrentlyWorking: true,
            position: 'Analista de RH Pleno',
            skills: ['Gupy', 'LinkedIn Recruiter'],
            startDate: Date::parse('2023-03-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto])
        );

        $record = WorkExperience::query()->firstOrFail();

        expect($record->position)->toBe('Analista de RH Pleno')
            ->and($record->metadata->skills)->toBe(['Gupy', 'LinkedIn Recruiter']);
    });

    it('persists a null position when the model did not extract one', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Nubank',
            description: 'Recrutamento',
            isCurrentlyWorking: false,
            startDate: Date::parse('2023-03-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto])
        );

        $record = WorkExperience::query()->firstOrFail();

        expect($record->position)->toBeNull()
            ->and($record->metadata->skills)->toBe([]);
    });

    it('persists an experience even when the description is empty', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Nubank',
            description: '',
            isCurrentlyWorking: false,
            position: 'Analista de RH',
            startDate: Date::parse('2023-03-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto])
        );

        assertDatabaseCount(WorkExperience::class, 1);
        assertDatabaseHas(WorkExperience::class, ['company_name' => 'Nubank', 'description' => '']);
    });

    it('skips an experience without a company name', function (): void {
        $dto = new CandidateWorkExperienceDTO(
            companyName: '',
            description: 'Alguma coisa',
            isCurrentlyWorking: false,
            startDate: Date::parse('2023-03-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto])
        );

        assertDatabaseCount(WorkExperience::class, 0);
    });

    it('does not overwrite an existing experience on cv re-upload', function (): void {
        // O UserObserver já cria um Candidate para cada User, então o fixture deixa dois
        // candidatos para o mesmo usuário. A Action resolve via `auth()->user()->candidate`,
        // e o registro pré-existente precisa pertencer a esse mesmo candidato.
        $existing = WorkExperience::factory()
            ->for($this->user->candidate, 'candidate')
            ->create([
                'company_name' => 'Nubank',
                'start_date' => Date::parse('2023-03-01')->startOfDay(),
                'position' => 'Cargo digitado pelo candidato',
            ]);

        $dto = new CandidateWorkExperienceDTO(
            companyName: 'Nubank',
            description: 'Recrutamento',
            isCurrentlyWorking: false,
            position: 'Cargo extraído pela IA',
            startDate: Date::parse('2023-03-01'),
        );

        resolve(StoreCandidateWorkExperiences::class)->execute(
            new CandidateWorkExperienceCollection([$dto])
        );

        assertDatabaseCount(WorkExperience::class, 1);
        expect($existing->fresh()->position)->toBe('Cargo digitado pelo candidato');
    });
});

describe('StoreCandidateEducation', function (): void {
    it('creates a new education record', function (): void {
        $dto = new CandidateEducationDTO(
            institution: 'MIT',
            degree: 'Bachelor',
            fieldOfStudy: 'Computer Science',
            isEnrolled: false,
            startDate: Date::parse('2020-01-01'),
            endDate: Date::parse('2024-01-01'),
        );

        resolve(StoreCandidateEducation::class)->execute(
            new CandidateEducationCollection([$dto])
        );

        assertDatabaseCount(Education::class, 1);
        assertDatabaseHas(Education::class, [
            'institution' => 'MIT',
            'degree' => 'Bachelor',
            'field_of_study' => 'Computer Science',
            'is_enrolled' => false,
        ]);
    });

    it('does not create duplicate when institution, degree and field_of_study match', function (): void {
        $dto = new CandidateEducationDTO(
            institution: 'FATEC',
            degree: 'Bacharelado',
            fieldOfStudy: 'ADS',
            isEnrolled: true,
            startDate: Date::parse('2022-01-01'),
        );

        $collection = new CandidateEducationCollection([$dto]);

        // First call
        resolve(StoreCandidateEducation::class)->execute($collection);
        assertDatabaseCount(Education::class, 1);

        // Second call with same data
        resolve(StoreCandidateEducation::class)->execute($collection);
        assertDatabaseCount(Education::class, 1);
    });

    it('creates a new record when institution is different', function (): void {
        $dto1 = new CandidateEducationDTO(
            institution: 'University A',
            degree: 'Bachelor',
            fieldOfStudy: 'Physics',
            isEnrolled: false,
            startDate: Date::parse('2020-01-01'),
        );

        $dto2 = new CandidateEducationDTO(
            institution: 'University B',
            degree: 'Bachelor',
            fieldOfStudy: 'Physics',
            isEnrolled: false,
            startDate: Date::parse('2020-01-01'),
        );

        resolve(StoreCandidateEducation::class)->execute(
            new CandidateEducationCollection([$dto1])
        );

        resolve(StoreCandidateEducation::class)->execute(
            new CandidateEducationCollection([$dto2])
        );

        assertDatabaseCount(Education::class, 2);
    });

    it('creates a new record when degree is different', function (): void {
        $dto1 = new CandidateEducationDTO(
            institution: 'Stanford',
            degree: 'Bachelor',
            fieldOfStudy: 'Engineering',
            isEnrolled: false,
            startDate: Date::parse('2020-01-01'),
        );

        $dto2 = new CandidateEducationDTO(
            institution: 'Stanford',
            degree: 'Master',
            fieldOfStudy: 'Engineering',
            isEnrolled: false,
            startDate: Date::parse('2020-01-01'),
        );

        resolve(StoreCandidateEducation::class)->execute(
            new CandidateEducationCollection([$dto1])
        );

        resolve(StoreCandidateEducation::class)->execute(
            new CandidateEducationCollection([$dto2])
        );

        assertDatabaseCount(Education::class, 2);
    });
});
