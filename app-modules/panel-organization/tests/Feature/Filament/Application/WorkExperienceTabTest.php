<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    JobPosting::factory()->for($this->application->requisition, 'jobRequisition')->createOne();
    $this->team = $this->application->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('renders the work experience tab for a past job without an end date', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'is_currently_working_here' => false,
            'end_date' => null,
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

it('shows the stored position as the card heading', function (): void {
    $experience = WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH Pleno',
            'description' => 'Recrutamento e seleção de vagas de tecnologia.',
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee('Analista de RH Pleno')
        ->assertSee($experience->company_name);
});

it('falls back to a neutral label when the position is null', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create(['position' => null]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee(__('panel-organization::view.tabs.work_experience.professional_role_fallback'));
});

it('never derives a heading from the description', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => null,
            'description' => 'Analista de RH',
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee(__('panel-organization::view.tabs.work_experience.professional_role_fallback'));
});

it('renders the description in full, without dropping the first line', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH',
            'description' => "Coordenacao de processos\nConducao de entrevistas",
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee('Coordenacao de processos')
        ->assertSee('Conducao de entrevistas');
});

it('renders skills from metadata instead of guessing them from the description', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'position' => 'Analista de RH',
            'description' => 'Acompanhei times que usavam Laravel no dia a dia.',
            'metadata' => new WorkExperienceMetadata(['Gupy']),
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertSee('Gupy')
        ->assertDontSee('>Laravel<', escape: false);
});
