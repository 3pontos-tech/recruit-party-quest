<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
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

    /**
     * @var array<int, EmploymentTypeEnum>
     */
    #[Url]
    public array $jobTypes = [];

    /**
     * @return LengthAwarePaginator<int, JobRequisition>
     */
    #[Computed]
    public function jobs(): LengthAwarePaginator
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
        return view('panel-app::livewire.job-recommendations');
    }
}
