<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\Jobs;

use He4rt\Candidates\Models\CandidateJobSaved;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BookmarkJobButton extends Component
{
    public JobRequisition $job;

    public bool $isSaved = false;

    public function mount(): void
    {
        if (auth()->check() && auth()->user()->candidate) {
            $this->isSaved = CandidateJobSaved::query()->where('candidate_id', auth()->user()->candidate->id)
                ->where('job_requisition_id', $this->job->id)
                ->exists();
        }
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('filament.app.auth.login'));

            return;
        }

        $candidate = auth()->user()->candidate;

        if (! $candidate) {
            return;
        }

        $existing = CandidateJobSaved::query()->where('candidate_id', $candidate->id)
            ->where('job_requisition_id', $this->job->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->isSaved = false;
        } else {
            CandidateJobSaved::query()->create([
                'candidate_id' => $candidate->id,
                'job_requisition_id' => $this->job->id,
            ]);
            $this->isSaved = true;
        }

        $this->dispatch('saved-job-toggled');
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.jobs.bookmark-job-button');
    }
}
