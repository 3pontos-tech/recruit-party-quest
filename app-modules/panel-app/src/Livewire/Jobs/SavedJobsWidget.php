<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\Jobs;

use DateTimeInterface;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Candidates\Models\SavedJob;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class SavedJobsWidget extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $savedJobs = [];

    public int $savedJobsCount = 0;

    public bool $loaded = false;

    public function mount(): void
    {
        $this->loadCount();
    }

    public function loadJobs(): void
    {
        $this->loadSavedJobs();
        $this->loadCount();
        $this->loaded = true;
    }

    #[On('saved-job-toggled')]
    public function handleJobToggled(): void
    {
        $this->loadCount();
        if ($this->loaded) {
            $this->loadSavedJobs();
        }
    }

    public function remove(string $jobRequisitionId): void
    {
        $candidate = auth()->user()?->candidate;

        if (! $candidate) {
            return;
        }

        SavedJob::query()->where('candidate_id', $candidate->id)
            ->where('job_requisition_id', $jobRequisitionId)
            ->delete();

        $this->loadCount();
        $this->loadSavedJobs();
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.jobs.saved-jobs-widget');
    }

    private function loadCount(): void
    {
        if (! auth()->check() || ! auth()->user()->candidate) {
            $this->savedJobsCount = 0;

            return;
        }

        $this->savedJobsCount = auth()->user()->candidate->savedJobs()->count();
    }

    private function loadSavedJobs(): void
    {
        if (! auth()->check() || ! auth()->user()->candidate) {
            $this->savedJobs = [];

            return;
        }

        $this->savedJobs = auth()->user()->candidate
            ->savedJobs()
            ->with([
                'jobRequisition' => fn ($q) => $q->withCount('applications')->with(['post', 'team', 'department']),
            ])
            ->get()
            ->map(fn (SavedJob $saved) => $this->formatJobData($saved))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatJobData(SavedJob $saved): array
    {
        /** @var JobRequisition $job */
        $job = $saved->jobRequisition;

        $salaryRange = null;
        if ($job->show_salary_to_candidates && ! is_null($job->salary_range_min) && ! is_null($job->salary_range_max)) {
            $salaryRange = $job->salary_currency.' '
                .number_format($job->salary_range_min, 0, ',', '.')
                .' - '
                .number_format($job->salary_range_max, 0, ',', '.');
        }

        return [
            'id' => (string) $job->getKey(),
            'title' => $job->post->title,
            'company' => $job->team->name,
            'url' => JobRequisitionResource::getUrl('view', ['record' => $job]),
            'workArrangement' => $job->work_arrangement->getLabel(),
            'employmentType' => $job->employment_type->getLabel(),
            'experienceLevel' => $job->experience_level->getLabel(),
            'department' => $job->department->name,
            'category' => $job->category?->getLabel(),
            'salaryRange' => $salaryRange,
            'publishedAt' => $job->published_at instanceof DateTimeInterface ? $job->published_at->format('d/m/Y') : null,
            'applicationsCount' => $job->applications_count ?? 0,
            'savedAt' => $saved->saved_at->format('d/m/Y'),
        ];
    }
}
