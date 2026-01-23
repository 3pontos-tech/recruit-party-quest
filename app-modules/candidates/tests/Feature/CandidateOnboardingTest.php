<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\WorkExperience;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    actingAs($this->candidate->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
    instanceFakeClass();
    $this->dto = generateDto();
    Storage::fake('public');
    $this->dto = [
        'fields' => [
            'education' => $this->dto->education,
            'work_experiences' => $this->dto->work_experiences,
        ]];
    $this->file = UploadedFile::fake()->create('curriculum.pdf');
});
it('render', function (): void {
    livewire(OnboardingWizard::class)
        ->assertOk();
});

test('candidate can not see other pages until onboarding was made ', function (): void {
    $this->candidate->update(['is_onboarded' => false]);
    get('/')
        ->assertRedirectToRoute('filament.app.pages.onboarding');

});

describe('wizard steps', function (): void {
    it('should set user email on getAccountStep ', function (): void {
        $livewire = livewire(OnboardingWizard::class)
            ->assertOk()
            ->getData();
        $data = $livewire['data'];
        assertequals($data['email'], $this->candidate->user->email);
    });

    it('should set fields after uploading curriculum', function (): void {
        $livewire = livewire(OnboardingWizard::class)
            ->call('onResumeAnalyzed', $this->dto)
            ->assertOk();

        $componentData = $livewire->getData();
        $componentData = $componentData['data'];

        $education = Arr::first($componentData['education']);

        assertEquals($education['institution'], 'FATEC');
        assertEquals($education['degree'], 'Bacharelado');
        assertEquals($education['field_of_study'], 'Analise Desenvolvimento de Sistemas');
        assertEquals($education['is_enrolled'], true);
        assertEquals($education['start_date'], '2024-08-01');
        assertEquals($education['end_date'], '2027-08-01');

        $work_experience = Arr::first($componentData['work_experiences']);
        assertEquals($work_experience['company_name'], '3-Pontos');
        assertEquals($work_experience['description'], 'working with php, filament, writing some tests');
        assertEquals($work_experience['start_date'], '2024-08-01');
        assertEquals($work_experience['end_date'], null);
        assertEquals($work_experience['is_currently_working_here'], true);
    });
});

it('should be able to onboard', function (): void {
    livewire(OnboardingWizard::class)
        ->set('data.cv_file', [$this->file])
        ->set('data.expected_salary', '1500')
        ->set('data.expected_salary_currency', 'USD')
        ->set('data.availability_date', now()->subDay())
        ->set('data.willing_to_relocate', true)
        ->set('data.is_open_to_remote', true)
        ->set('data.experience_level', 'intern')
        ->set('data.employment_type_interests', 'whatever')
        ->set('data.confirm_submission', true)
        ->set('data.data_consent_given', true)
        ->call('onResumeAnalyzed', $this->dto)
        ->call('handleRegistration')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->assertRedirectToRoute('filament.app.pages.app-dashboard');

    assertDatabaseHas(Candidate::class, [
        'experience_level' => 'intern',
        'expected_salary' => 1500,
        'is_open_to_remote' => 1,
        'willing_to_relocate' => 1,
    ]);
    assertDatabaseCount(Education::class, 1);
    assertDatabaseHas(Education::class, [
        'institution' => 'FATEC',
        'degree' => 'Bacharelado',
        'field_of_study' => 'Analise Desenvolvimento de Sistemas',
        'is_enrolled' => true,
    ]);
    assertDatabaseCount(WorkExperience::class, 1);
    assertDatabaseHas(WorkExperience::class, [
        'company_name' => '3-Pontos',
        'description' => 'working with php, filament, writing some tests',
        'is_currently_working_here' => true,
    ]);
});

it('should disable file uploader when it is uploading a file', function (): void {
    livewire(OnboardingWizard::class)
        ->set('data.cv_file', [$this->file])
        ->call('onResumeAnalyzed', $this->dto)
        ->assertOk()
        ->assertHasNoFormErrors()
        ->assertDontSeeText(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_helper'))
        ->assertNotified(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_uploading'));
});

test('after uploading the resume, should see wizard steps', function (): void {
    livewire(OnboardingWizard::class)
        ->set('data.cv_file', [$this->file])
        ->call('onResumeAnalyzed', $this->dto)
        ->assertOk()
        ->assertHasNoFormErrors()
        ->assertDontSeeText(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_helper'))
        ->assertSeeText(__('panel-app::pages/onboarding.steps.profile.sections.work_experience'))
        ->assertSeeText(__('panel-app::pages/onboarding.steps.preferences.fields.expected_salary'))
        ->assertSeeText(__('panel-app::pages/onboarding.steps.review.sections.review_summary'));
});

function instanceFakeClass(): void
{
    app()->bind(AiAutocompleteInterface::class, fn () => new class implements AiAutocompleteInterface
    {
        public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
        {
            return generateDto();
        }
    });
}

function generateDto(): CandidateOnboardingDTO
{
    $education = new CandidateEducationDTO(
        institution: 'FATEC',
        degree: 'Bacharelado',
        fieldOfStudy: 'Analise Desenvolvimento de Sistemas',
        isEnrolled: true,
        startDate: Date::parse('08/01/2024'),
        endDate: Date::parse('08/01/2027'),
    );
    $work = new CandidateWorkExperienceDTO(
        companyName: '3-Pontos',
        description: 'working with php, filament, writing some tests',
        isCurrentlyWorking: true,
        startDate: Date::parse('08/01/2024'),
    );

    return CandidateOnboardingDTO::make([
        'education' => [$education],
        'work_experiences' => [$work],
    ]);
}
