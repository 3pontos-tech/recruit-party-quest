<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages\RepoAnalysis;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class RepoAnalysisListPage extends Page
{
    /** @var Collection<int, RepositoryAnalysis> */
    public Collection $analyses;

    public string $userId = '';

    public bool $hasGitHubConnection = false;

    public ?string $newAnalysisDisabledReason = null;

    protected static ?string $slug = 'repositories';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CodeBracket;

    protected static ?string $navigationLabel = 'Análise de Código';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->candidate()->exists() ?? false;
    }

    public function mount(): void
    {
        $this->userId = (string) auth()->id();

        $user = auth()->user();

        $this->hasGitHubConnection = $user?->githubConnection !== null;

        $candidate = $user?->candidate()->first();

        $this->analyses = $candidate !== null
            ? RepositoryAnalysis::query()
                ->forCandidate($candidate)
                ->where('status', '!=', AnalysisStatus::Failed)
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $this->newAnalysisDisabledReason = $this->resolveNewAnalysisBlockReason();
    }

    public function getView(): string
    {
        return 'panel-app::filament.pages.repo-analysis.list';
    }

    public function getHeading(): string
    {
        return __('repo-analysis::labels.page.list.heading');
    }

    public function getSubheading(): ?string
    {
        if (count($this->analyses) === 1) {
            return __('repo-analysis::labels.components.analysis_grid.count_singular',
                ['count' => count($this->analyses)]);
        }

        return __('repo-analysis::labels.components.analysis_grid.count_plural', ['count' => count($this->analyses)]);
    }

    public function hasInProgressAnalyses(): bool
    {
        return $this->analyses->contains(
            fn (RepositoryAnalysis $a): bool => in_array($a->status->value, ['pending', 'analyzing'])
        );
    }

    #[On('echo-private:repo-analysis.{userId},.completed')]
    #[On('echo-private:repo-analysis.{userId},.failed')]
    public function refreshAnalyses(): void
    {
        $this->mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_analysis')
                ->label(__('repo-analysis::labels.actions.new_analysis'))
                ->icon(Heroicon::Plus)
                ->color('success')
                ->disabled(fn (): bool => $this->newAnalysisDisabledReason !== null)
                ->tooltip(fn (): ?string => $this->newAnalysisDisabledReason)
                ->url(fn (): ?string => $this->newAnalysisDisabledReason === null
                    ? NewRepoAnalysisPage::getUrl()
                    : null
                ),
        ];
    }

    private function resolveNewAnalysisBlockReason(): ?string
    {
        $user = auth()->user();

        if ($user?->githubConnection === null) {
            return __('repo-analysis::labels.actions.disabled.no_github');
        }

        if ($this->hasInProgressAnalyses()) {
            return __('repo-analysis::labels.actions.disabled.in_progress');
        }

        $candidate = $user->candidate()->first();
        if ($candidate !== null) {
            $lastAnalysis = RepositoryAnalysis::query()
                ->forCandidate($candidate)
                ->whereNotNull('analyzed_at')
                ->latest('analyzed_at')
                ->first();

            if ($lastAnalysis !== null && $lastAnalysis->isOnCooldown()) {
                $daysRemaining = (int) now()->diffInDays(
                    $lastAnalysis->analyzed_at->addDays(7)
                );

                return trans_choice(
                    'repo-analysis::labels.actions.disabled.cooldown',
                    max(1, $daysRemaining),
                    ['days' => max(1, $daysRemaining)]
                );
            }
        }

        return null;
    }
}
