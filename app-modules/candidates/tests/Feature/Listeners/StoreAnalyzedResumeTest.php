<?php

declare(strict_types=1);

use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use He4rt\Candidates\Events\AnalyzeResumeEvent;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    // O evento é ShouldBroadcast: o fake impede a transmissão sem impedir os listeners
    // locais, que é justamente o caminho sob teste.
    Queue::fake();

    $this->user = User::factory()->create();
});

function analyzedResume(array $fields = []): CandidateOnboardingDTO
{
    return CandidateOnboardingDTO::make([
        'work_experiences' => [
            [
                'company_name' => 'Nubank',
                'position' => 'Analista de RH',
                'start_date' => '2023-03-01',
            ],
        ],
        'education' => [],
        ...$fields,
    ]);
}

it('persists the analysis without the browser for an onboarded candidate', function (): void {
    $candidate = candidateFor($this->user, ['is_onboarded' => true, 'cv_last_uploaded_at' => null]);

    event(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, analyzedResume(), $this->user->getKey()));

    assertDatabaseHas(WorkExperience::class, [
        'candidate_id' => $candidate->getKey(),
        'company_name' => 'Nubank',
    ]);

    expect($candidate->fresh()->cv_last_uploaded_at)->not->toBeNull();
});

it('leaves the onboarding analysis for the candidate to review', function (): void {
    candidateFor($this->user, ['is_onboarded' => false]);

    event(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, analyzedResume(), $this->user->getKey()));

    assertDatabaseCount(WorkExperience::class, 0);
});

it('ignores a user without a candidate profile', function (): void {
    event(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, analyzedResume(), $this->user->getKey()));

    assertDatabaseCount(WorkExperience::class, 0);
});

it('ignores every status other than finished', function (ResumeAnalyzeStatus $status): void {
    candidateFor($this->user, ['is_onboarded' => true]);

    event(new AnalyzeResumeEvent($status, analyzedResume(), $this->user->getKey()));

    assertDatabaseCount(WorkExperience::class, 0);
})->with([
    'queued' => ResumeAnalyzeStatus::Queued,
    'processing' => ResumeAnalyzeStatus::Processing,
    'error' => ResumeAnalyzeStatus::Error,
]);

it('ignores a finished event that carries no fields', function (): void {
    candidateFor($this->user, ['is_onboarded' => true]);

    event(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, null, $this->user->getKey()));

    assertDatabaseCount(WorkExperience::class, 0);
});

it('does not overwrite what the candidate had already filled in', function (): void {
    $candidate = candidateFor($this->user, ['is_onboarded' => true]);

    $existing = WorkExperience::factory()
        ->for($candidate, 'candidate')
        ->create([
            'company_name' => 'Nubank',
            'start_date' => now()->parse('2023-03-01')->startOfDay(),
            'position' => 'Cargo digitado pelo candidato',
        ]);

    event(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Finished, analyzedResume(), $this->user->getKey()));

    assertDatabaseCount(WorkExperience::class, 1);
    expect($existing->fresh()->position)->toBe('Cargo digitado pelo candidato');
});
