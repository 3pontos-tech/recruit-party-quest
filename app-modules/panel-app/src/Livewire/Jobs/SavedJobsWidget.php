<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\Jobs;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use He4rt\Candidates\Models\CandidateJobSaved;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class SavedJobsWidget extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public int $savedJobsCount = 0;

    public function mount(): void
    {
        $this->loadCount();
    }

    #[On('saved-job-toggled')]
    public function handleJobToggled(): void
    {
        $this->loadCount();
    }

    public function viewSavedJobsAction(): Action
    {
        return Action::make('viewSavedJobs')
            ->label(__('panel-app::filament.components.saved_jobs_widget.title'))
            ->icon(Heroicon::Bookmark)
            ->iconButton()
            ->color('gray')
            ->badge($this->savedJobsCount ?: null)
            ->extraAttributes([
                'aria-label' => __('panel-app::filament.components.saved_jobs_widget.aria_label'),
                'data-test' => 'saved-jobs-badge',
            ])
            ->slideOver()
            ->modalWidth(Width::Medium)
            ->modalHeading(__('panel-app::filament.components.saved_jobs_widget.title'))
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(fn (): View => view('panel-app::components.jobs.saved-jobs-list', [
                'jobs' => $this->savedJobs(),
            ]));
    }

    public function removeSavedJob(string $jobRequisitionId): void
    {
        $candidate = auth()->user()?->candidate;

        if (! $candidate) {
            return;
        }

        CandidateJobSaved::query()
            ->where('candidate_id', $candidate->id)
            ->where('job_requisition_id', $jobRequisitionId)
            ->delete();

        $this->loadCount();
        $this->dispatch('saved-job-toggled');
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.jobs.saved-jobs-widget');
    }

    private function loadCount(): void
    {
        $candidate = auth()->user()?->candidate;

        $this->savedJobsCount = $candidate
            ? $candidate->bookmarkedJobs()->withPublishedRequisition()->count()
            : 0;
    }

    /**
     * @return Collection<int, CandidateJobSaved>
     */
    private function savedJobs(): Collection
    {
        $candidate = auth()->user()?->candidate;

        if (! $candidate) {
            return new Collection();
        }

        return $candidate->bookmarkedJobs()
            ->withPublishedRequisition()
            ->with([
                'jobRequisition' => fn ($query) => $query
                    ->withCount('applications')
                    ->with(['post', 'team', 'department']),
            ])
            ->get();
    }
}
