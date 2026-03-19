<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages\RepoAnalysis;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use He4rt\Candidates\Models\Candidate;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Exceptions\GitHubException;
use He4rt\RepoAnalysis\Jobs\AnalyzeRepositoryJob;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use He4rt\RepoAnalysis\Services\GitHubService;
use Illuminate\Http\Client\ConnectionException;

class NewRepoAnalysisPage extends Page
{
    /** @var array<int, array{name: string, full_name: string, html_url: string, default_branch: string, language: string|null, private: bool}> */
    public array $repos = [];

    public bool $hasGitHubConnection = false;

    protected static ?string $slug = 'repositories/new';

    protected static string|BackedEnum|null $navigationIcon = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(GitHubService $github): void
    {
        $user = auth()->user();
        $connection = $user?->githubConnection;

        if ($connection === null) {
            $this->hasGitHubConnection = false;

            return;
        }

        $this->hasGitHubConnection = true;

        try {
            $this->repos = $github->listRepositories($connection->access_token);
        } catch (ConnectionException|GitHubException) {
            Notification::make()
                ->warning()
                ->title(__('repo-analysis::labels.notifications.github_unavailable'))
                ->send();

            $this->redirect(RepoAnalysisListPage::getUrl());

            return;
        }

        $candidate = $user->candidate()->first();
        if ($candidate !== null) {
            $lastAnalysis = $this->resolveCooldown($candidate);

            if ($lastAnalysis instanceof RepositoryAnalysis && $lastAnalysis->isOnCooldown()) {
                $daysRemaining = (int) now()->diffInDays(
                    $lastAnalysis->analyzed_at->addDays(7)
                );

                Notification::make()
                    ->warning()
                    ->title(trans_choice(
                        'repo-analysis::labels.notifications.cooldown_redirect',
                        max(1, $daysRemaining),
                        ['days' => max(1, $daysRemaining)]
                    ))
                    ->send();

                $this->redirect(RepoAnalysisListPage::getUrl());

                return;
            }
        }
    }

    public function submitAnalysis(string $repoFullName): void
    {
        if (! $this->hasGitHubConnection) {
            return;
        }

        $selectedRepo = collect($this->repos)
            ->first(fn (array $repo): bool => $repo['full_name'] === $repoFullName);

        if ($selectedRepo === null) {
            return;
        }

        $candidate = auth()->user()?->candidate;
        if ($candidate === null) {
            return;
        }

        $hasInProgress = RepositoryAnalysis::query()
            ->forCandidate($candidate)
            ->whereIn('status', [AnalysisStatus::Pending, AnalysisStatus::Analyzing])
            ->exists();

        if ($hasInProgress) {
            Notification::make()
                ->warning()
                ->title(__('repo-analysis::labels.notifications.analysis_in_progress'))
                ->send();

            return;
        }

        $lastAnalysis = $this->resolveCooldown($candidate);

        if ($lastAnalysis instanceof RepositoryAnalysis && $lastAnalysis->isOnCooldown()) {
            $daysRemaining = (int) now()->diffInDays(
                $lastAnalysis->analyzed_at->addDays(7)
            );

            Notification::make()
                ->warning()
                ->title(trans_choice(
                    'repo-analysis::labels.notifications.cooldown_redirect',
                    max(1, $daysRemaining),
                    ['days' => max(1, $daysRemaining)]
                ))
                ->send();

            $this->redirect(RepoAnalysisListPage::getUrl());

            return;
        }

        $analysis = RepositoryAnalysis::query()->create([
            'candidate_id' => $candidate->id,
            'repo_name' => $selectedRepo['name'],
            'repo_full_name' => $selectedRepo['full_name'],
            'repo_url' => $selectedRepo['html_url'],
            'repo_default_branch' => $selectedRepo['default_branch'],
            'repo_language' => $selectedRepo['language'],
            'repo_is_private' => $selectedRepo['private'],
        ]);

        dispatch(new AnalyzeRepositoryJob($analysis));

        Notification::make()
            ->success()
            ->title(__('repo-analysis::labels.notifications.analysis_started'))
            ->send();

        $this->redirect(RepoAnalysisListPage::getUrl());
    }

    public function getView(): string
    {
        return 'panel-app::filament.pages.repo-analysis.new';
    }

    public function getHeading(): string
    {
        return __('repo-analysis::labels.page.new.heading');
    }

    private function resolveCooldown(Candidate $candidate): ?RepositoryAnalysis
    {
        return RepositoryAnalysis::query()
            ->forCandidate($candidate)
            ->whereNotNull('analyzed_at')
            ->latest('analyzed_at')
            ->first();
    }
}
