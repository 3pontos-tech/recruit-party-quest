<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelIdea\Helper\He4rt\Recruitment\Requisitions\Models\_IH_JobRequisition_C;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JobRecommendations extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url]
    public string $workModel = '';

    #[Url]
    public array $jobTypes = [];

    public function jobsAvailable(): array|_IH_JobRequisition_C|LengthAwarePaginator
    {
        return JobRequisition::query()
            ->with('post')
            ->withCount('applications')
            ->when($this->search, fn ($q) => $q->whereHas('post', fn (Builder $p) => $p->where('title', 'like', sprintf('%%%s%%', $this->search))))
            ->when($this->workModel, fn ($q) => $q->where('work_arrangement', $this->workModel))
            ->unless($this->jobTypes === [], fn ($q) => $q->whereIn('employment_type', $this->jobTypes))
            ->latest()
            ->paginate(12);
    }

    public function render(): View
    {
        return view('panel-app::livewire.job-recommendations', [
            'jobs' => $this->jobsAvailable(),
        ]);
    }
}
