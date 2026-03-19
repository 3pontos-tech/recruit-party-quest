<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\RepoAnalysis\NewRepoAnalysisPage;
use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisListPage;
use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisResultPage;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\Users\Models\UserGitHubConnection;
use He4rt\Users\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = $this->user->candidate()->first();

    actingAs($this->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);

    Queue::fake();
});

it('renders the list page successfully', function (): void {
    livewire(RepoAnalysisListPage::class)
        ->assertOk();
});

it('shows analyses belonging to the authenticated user', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    $analyses = RepositoryAnalysis::factory()->count(2)->create([
        'candidate_id' => $this->candidate->id,
    ]);

    // Verify analyses were created
    expect(RepositoryAnalysis::query()->count())->toBe(2);
    expect($this->candidate->id)->toBe($analyses[0]->candidate_id);

    livewire(RepoAnalysisListPage::class)
        ->assertDontSee(__('repo-analysis::labels.page.list.empty_heading'));
});

it('does not show analyses from other candidates', function (): void {
    $otherUser = User::factory()->create();
    $otherAnalysis = RepositoryAnalysis::factory()->create([
        'candidate_id' => $otherUser->candidate()->first()->id,
    ]);

    livewire(RepoAnalysisListPage::class)
        ->assertDontSee($otherAnalysis->repo_full_name);
});

it('shows empty state when no analyses exist', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    livewire(RepoAnalysisListPage::class)
        ->assertSee(__('repo-analysis::labels.page.list.empty_heading'));
});

it('shows github connect prompt when github is not connected', function (): void {
    livewire(RepoAnalysisListPage::class)
        ->assertSee(__('repo-analysis::labels.page.list.no_github.heading'))
        ->assertSee(__('repo-analysis::labels.page.list.no_github.button'))
        ->assertDontSee(__('repo-analysis::labels.page.list.empty_heading'));
});

it('has a new analysis header action that links to the new page', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    livewire(RepoAnalysisListPage::class)
        ->assertSee(__('repo-analysis::labels.actions.new_analysis'))
        ->assertSeeHtml(NewRepoAnalysisPage::getUrl());
});

it('has a view link on each analysis card', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    $analysis = RepositoryAnalysis::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $resultUrl = RepoAnalysisResultPage::getUrl(['uuid' => $analysis->id]);

    livewire(RepoAnalysisListPage::class)
        ->assertSee($analysis->repo_name)
        ->assertSeeHtml($resultUrl);
});
