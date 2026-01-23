<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Retrieves and filters the user latest applications.
 */
class SearchJobs extends Component
{
    use WithPagination;

    public string $query;

    #[Url]
    public string $search = '';

    #[Url]
    public string $location = '';

    public int $perPage = 4;

    #[Computed]
    public function user(): User
    {
        return auth()->user();
    }

    /**
     * @return LengthAwarePaginator<int, JobRequisition>
     */
    #[Computed]
    public function jobs(): LengthAwarePaginator
    {
        $baseQuery = JobRequisition::query();

        if ($this->query !== '' && $this->query !== '0') {
            $baseQuery->fromRaw(sprintf('(%s) as recruitment_job_requisitions', $this->query));
        }

        return $baseQuery
            ->when($this->search, function ($query): void {
                $query->whereHas('post', function (Builder $q): void {
                    $q->where('title', 'like', sprintf('%%%s%%', $this->search))
                        ->orWhere('summary', 'like', sprintf('%%%s%%', $this->search));
                });
            })
            ->with(['post', 'team'])
            ->paginate($this->perPage);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.searchJobs');
    }
}
