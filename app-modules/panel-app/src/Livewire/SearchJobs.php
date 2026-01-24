<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
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

    #[Url]
    public string $search = '';

    /**
     * @var array<int, WorkArrangementEnum>
     */
    #[Url]
    public array $workArrangements = [];

    /**
     * @var array<int, EmploymentTypeEnum>
     */
    #[Url]
    public array $employmentTypes = [];

    #[Url]
    public ?ExperienceLevelEnum $experienceLevel = null;

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
        return JobRequisition::query()
            ->when($this->search, function ($query): void {
                $query->whereHas('post', function (Builder $q): void {
                    $q->where('title', 'like', sprintf('%%%s%%', $this->search))
                        ->orWhere('summary', 'like', sprintf('%%%s%%', $this->search));
                });
            })
            ->when($this->workArrangements, function ($query): void {
                $query->whereIn('work_arrangement', $this->workArrangements);
            })
            ->when($this->employmentTypes, function ($query): void {
                $query->whereIn('employment_type', $this->employmentTypes);
            })
            ->when($this->experienceLevel, function ($query): void {
                $query->where('experience_level', $this->experienceLevel);
            })
            ->when($this->location, function ($query): void {
                $query->where(function (Builder $q): void {
                    $q->whereHas('team.address', function (Builder $q): void {
                        $q->where('city', 'like', sprintf('%%%s%%', $this->location))
                            ->orWhere('state', 'like', sprintf('%%%s%%', $this->location))
                            ->orWhere('country', 'like', sprintf('%%%s%%', $this->location));
                    });

                    if (str_contains(mb_strtolower($this->location), 'remote')) {
                        $q->orWhere('work_arrangement', WorkArrangementEnum::Remote);
                    }
                });
            })
            ->with(['post', 'team'])
            ->latest()
            ->paginate($this->perPage);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingWorkArrangements(): void
    {
        $this->resetPage();
    }

    public function updatingEmploymentTypes(): void
    {
        $this->resetPage();
    }

    public function updatingExperienceLevel(): void
    {
        $this->resetPage();
    }

    public function updatingLocation(): void
    {
        $this->resetPage();
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.searchJobs');
    }
}
