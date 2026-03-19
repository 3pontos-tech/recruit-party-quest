<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\RepoAnalysis\NewRepoAnalysisPage;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\RepoAnalysis\Services\GitHubService;
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

    $this->fakeRepos = [
        [
            'name' => 'my-project',
            'full_name' => 'johndoe/my-project',
            'html_url' => 'https://github.com/johndoe/my-project',
            'default_branch' => 'main',
            'language' => 'PHP',
            'private' => false,
        ],
    ];
});

it('shows the connect github prompt when no connection exists', function (): void {
    livewire(NewRepoAnalysisPage::class)
        ->assertSet('hasGitHubConnection', false)
        ->assertSee('github');
});

it('shows the repo selection form when github is connected', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('listRepositories')->once()->andReturn($this->fakeRepos);

    livewire(NewRepoAnalysisPage::class)
        ->assertSet('hasGitHubConnection', true)
        ->assertSet('repos', $this->fakeRepos);
});

it('sends warning notification and redirects when cooldown is active', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    RepositoryAnalysis::factory()->completed()->create([
        'candidate_id' => $this->candidate->getKey(),
        'repo_full_name' => 'johndoe/my-project',
        'analyzed_at' => now()->subDays(2),
    ]);

    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('listRepositories')->andReturn($this->fakeRepos);

    livewire(NewRepoAnalysisPage::class)
        ->assertNotified();

});

it('calculates cooldown correctly when a previous analysis exists outside cooldown period', function (): void {
    UserGitHubConnection::factory()->create(['user_id' => $this->user->getKey()]);

    $previous = RepositoryAnalysis::factory()->completed()->create([
        'candidate_id' => $this->candidate->getKey(),
        'repo_full_name' => 'johndoe/my-project',
        'analyzed_at' => now()->subDays(10),
    ]);

    $github = $this->mock(GitHubService::class);
    $github->shouldReceive('listRepositories')->andReturn($this->fakeRepos);

    livewire(NewRepoAnalysisPage::class)
        ->assertSet('cooldownUntil', null)
        ->assertDontSee(__('repo-analysis::labels.page.new.cooldown.heading'));
});
