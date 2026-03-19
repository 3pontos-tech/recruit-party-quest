<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages\RepoAnalysis;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use Livewire\Attributes\On;

class RepoAnalysisResultPage extends Page
{
    public RepositoryAnalysis $analysis;

    public string $userId = '';

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $slug = 'repositories/result/{uuid}';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(string $uuid): void
    {
        $this->userId = (string) auth()->id();

        $record = RepositoryAnalysis::query()->findOrFail($uuid);

        abort_if(
            $record->candidate->user_id !== auth()->id(),
            403
        );

        $this->analysis = $record;
    }

    public function getView(): string
    {
        return 'panel-app::filament.pages.repo-analysis.result';
    }

    public function getHeading(): string
    {
        return __('panel-app::filament.pages.repo_analysis_result.heading');
    }

    public function isAnalyzing(): bool
    {
        return $this->analysis->status === AnalysisStatus::Analyzing
            || $this->analysis->status === AnalysisStatus::Pending;
    }

    public function refreshStatus(): void
    {
        $this->analysis->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[On('echo-private:repo-analysis.{userId},.completed')]
    public function onAnalysisCompleted(array $data): void
    {
        if ($data['analysis_id'] !== $this->analysis->id) {
            return;
        }

        $this->analysis->refresh();

        Notification::make()
            ->success()
            ->title(__('repo-analysis::labels.notifications.analysis_completed', [
                'repo' => $this->analysis->repo_name,
            ]))
            ->send();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[On('echo-private:repo-analysis.{userId},.failed')]
    public function onAnalysisFailed(array $data): void
    {
        if ($data['analysis_id'] !== $this->analysis->id) {
            return;
        }

        $this->analysis->refresh();

        Notification::make()
            ->danger()
            ->title(__('repo-analysis::labels.notifications.analysis_failed'))
            ->send();
    }
}
