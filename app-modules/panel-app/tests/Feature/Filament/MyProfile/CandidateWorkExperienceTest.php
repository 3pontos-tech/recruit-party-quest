<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    // O UserObserver já cria um Candidate por User. Criar outro aqui deixaria dois
    // registros para o mesmo `user_id`, e `auth()->user()->candidate` — usado pelo
    // componente — resolveria para o do observer, não para o do fixture.
    $this->user->refresh();

    $this->candidate = $this->user->candidate;

    actingAs($this->user);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidateWorkExperience::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.work_experience.heading'));
});

it('renders with existing work experience data', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'company_name' => 'Acme Corp',
        ]);

    Livewire::test(CandidateWorkExperience::class)
        ->assertOk();

    expect($this->candidate->workExperiences()->count())->toBe(1);
});

it('can submit work experience form', function (): void {
    Livewire::test(CandidateWorkExperience::class)
        ->call('submit')
        ->assertOk();
});

it('loads position and skills from existing records', function (): void {
    WorkExperience::factory()->for($this->candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => 'Analista de RH Pleno',
        'metadata' => new WorkExperienceMetadata(['Gupy']),
    ]);

    Livewire::test(CandidateWorkExperience::class)
        ->assertFormSet(function (array $state): array {
            // O Repeater reindexa os itens com UUID ao hidratar, então a chave do item
            // não é `0` — é preciso lê-la do próprio state.
            $key = array_key_first($state['work_experiences']);

            return [
                sprintf('work_experiences.%s.position', $key) => 'Analista de RH Pleno',
                sprintf('work_experiences.%s.skills', $key) => ['Gupy'],
            ];
        });
});

it('saves without a position, so legacy records do not block the form', function (): void {
    WorkExperience::factory()->for($this->candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => null,
        'metadata' => null,
    ]);

    Livewire::test(CandidateWorkExperience::class)
        ->call('submit')
        ->assertHasNoFormErrors();
});

it('persists position and skills edited by the candidate', function (): void {
    $experience = WorkExperience::factory()->for($this->candidate, 'candidate')->create([
        'company_name' => 'Nubank',
        'position' => null,
        'metadata' => null,
    ]);

    Livewire::test(CandidateWorkExperience::class)
        ->fillForm([
            'work_experiences' => [
                [
                    'id' => $experience->id,
                    'company_name' => 'Nubank',
                    'position' => 'Analista de RH Sênior',
                    'description' => $experience->description,
                    'start_date' => $experience->start_date->format('Y-m-d'),
                    'end_date' => null,
                    'is_currently_working_here' => true,
                    'skills' => ['Gupy', 'Excel'],
                ],
            ],
        ])
        ->call('submit')
        ->assertHasNoFormErrors();

    expect($experience->fresh()->position)->toBe('Analista de RH Sênior')
        ->and($experience->fresh()->metadata->skills)->toBe(['Gupy', 'Excel']);
});
