<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisResultPage;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = $this->user->candidate()->first();

    actingAs($this->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders the result page for the owner', function (): void {
    $analysis = RepositoryAnalysis::factory()->completed()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    livewire(RepoAnalysisResultPage::class, ['uuid' => $analysis->id])
        ->assertOk();
});

it('returns 403 when accessing another user analysis', function (): void {
    $otherUser = User::factory()->create();
    $otherAnalysis = RepositoryAnalysis::factory()->completed()->create([
        'candidate_id' => $otherUser->candidate()->first()->id,
    ]);

    livewire(RepoAnalysisResultPage::class, ['uuid' => $otherAnalysis->id])
        ->assertForbidden();
});

it('shows loading state when analysis is pending', function (): void {
    $analysis = RepositoryAnalysis::factory()->create([
        'candidate_id' => $this->candidate->id,
        'status' => AnalysisStatus::Pending,
    ]);

    $component = Livewire::test(RepoAnalysisResultPage::class, ['uuid' => $analysis->id]);

    expect($component->instance()->isAnalyzing())->toBeTrue();
});

it('shows loading state when analysis is in progress', function (): void {
    $analysis = RepositoryAnalysis::factory()->analyzing()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $component = Livewire::test(RepoAnalysisResultPage::class, ['uuid' => $analysis->id]);

    expect($component->instance()->isAnalyzing())->toBeTrue();
});

it('does not show loading state for a completed analysis', function (): void {
    $analysis = RepositoryAnalysis::factory()->completed()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $component = Livewire::test(RepoAnalysisResultPage::class, ['uuid' => $analysis->id]);

    expect($component->instance()->isAnalyzing())->toBeFalse();
});

it('refreshes the analysis model when refreshStatus is called', function (): void {
    $analysis = RepositoryAnalysis::factory()->analyzing()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $component = Livewire::test(RepoAnalysisResultPage::class, ['uuid' => $analysis->id]);

    $analysis->update(['status' => AnalysisStatus::Completed]);
    $component->call('refreshStatus');

    expect($component->instance()->analysis->status)->toBe(AnalysisStatus::Completed);
});
