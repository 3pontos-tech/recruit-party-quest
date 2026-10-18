<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire\Applications;

use He4rt\Applications\Actions\BuildRequisitionApplicationStats;
use He4rt\Applications\Actions\BuildRequisitionFunnels;
use He4rt\Applications\DTOs\RequisitionApplicationStats;
use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Recruitment\Requisitions\Enums\RequisitionOverviewSort;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Actions\CountKnockoutQuestionsByRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Espaço do recrutador: panorama de vagas e, dentro de uma vaga, fila de candidaturas com prévia.
 *
 * @property-read LengthAwarePaginator<int, JobRequisition> $requisitionsPage
 * @property-read array<string, array<string, int>> $funnelByRequisition
 * @property-read array<string, int> $knockoutQuestionsByRequisition
 * @property-read JobRequisition|null $requisition
 * @property-read Collection<int, Stage> $stages
 * @property-read int $knockoutQuestionCount
 * @property-read RequisitionApplicationStats $stats
 * @property-read LengthAwarePaginator<int, Application> $applicationsPage
 * @property-read Application|null $selectedApplication
 * @property-read EloquentCollection<int, JobRequisition> $requisitionResults
 */
class ApplicationsWorkspace extends Component
{
    use WithPagination;

    public const int APPLICATIONS_PER_PAGE = 25;

    public const int REQUISITIONS_PER_PAGE = 20;

    public const string REQUISITIONS_PAGE_NAME = 'jobsPage';

    #[Locked]
    public string $teamId = '';

    #[Url(as: 'job', except: '')]
    public string $requisitionId = '';

    public string $requisitionSearch = '';

    public RequisitionOverviewSort $requisitionSort = RequisitionOverviewSort::New;

    public bool $onlyPublished = true;

    public string $search = '';

    public string $stageId = '';

    public ?ApplicationStatusGroup $statusGroup = null;

    public ScreeningVerdictFilter $screening = ScreeningVerdictFilter::All;

    public SeenFilter $seen = SeenFilter::All;

    public ApplicationListSort $sort = ApplicationListSort::Attention;

    public string $selectedId = '';

    public function mount(): void
    {
        $tenant = filament()->getTenant();
        abort_unless($tenant instanceof Model, 403);

        $this->teamId = (string) $tenant->getKey();

        if (! Str::isUuid($this->requisitionId)) {
            $this->requisitionId = '';
        }
    }

    /**
     * @return LengthAwarePaginator<int, JobRequisition>
     */
    #[Computed]
    public function requisitionsPage(): LengthAwarePaginator
    {
        $term = mb_trim($this->requisitionSearch);

        return JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with(['post', 'department', 'recruiter.user', 'stages' => fn ($stages) => $stages->where('active', true)->orderBy('display_order')])
            ->withRecruiterOverviewCounts()
            ->when($this->onlyPublished, fn (Builder $query) => $query->where('status', RequisitionStatusEnum::Published->value))
            ->when($term !== '', fn (Builder $query) => $query->searchTitle($term))
            ->orderForRecruiterOverview($this->requisitionSort)
            ->paginate(self::REQUISITIONS_PER_PAGE, pageName: self::REQUISITIONS_PAGE_NAME);
    }

    /**
     * @return array<string, array<string, int>>
     */
    #[Computed]
    public function funnelByRequisition(): array
    {
        $ids = collect($this->requisitionsPage->items())->map(fn (JobRequisition $requisition): string => (string) $requisition->getKey())->all();

        return resolve(BuildRequisitionFunnels::class)->execute($ids);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function knockoutQuestionsByRequisition(): array
    {
        return resolve(CountKnockoutQuestionsByRequisition::class)->execute(collect($this->requisitionsPage->items()));
    }

    #[Computed]
    public function requisition(): ?JobRequisition
    {
        if ($this->requisitionId === '' || ! Str::isUuid($this->requisitionId)) {
            return null;
        }

        $requisition = JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with(['post', 'department', 'recruiter.user', 'stages' => fn ($stages) => $stages->where('active', true)->orderBy('display_order')])
            ->find($this->requisitionId);

        if ($requisition === null) {
            $this->requisitionId = '';
        }

        return $requisition;
    }

    /**
     * @return Collection<int, Stage>
     */
    #[Computed]
    public function stages(): Collection
    {
        return $this->requisition->stages ?? new EloquentCollection();
    }

    #[Computed]
    public function knockoutQuestionCount(): int
    {
        $requisition = $this->requisition;

        if ($requisition === null) {
            return 0;
        }

        return resolve(CountKnockoutQuestionsByRequisition::class)->execute(collect([$requisition]))[(string) $requisition->getKey()] ?? 0;
    }

    #[Computed]
    public function stats(): RequisitionApplicationStats
    {
        $requisition = $this->requisition;

        if ($requisition === null) {
            return new RequisitionApplicationStats(0, 0, 0, 0, 0, 0, 0, 0, 0, []);
        }

        return resolve(BuildRequisitionApplicationStats::class)->execute($requisition);
    }

    /**
     * @return LengthAwarePaginator<int, Application>
     */
    #[Computed]
    public function applicationsPage(): LengthAwarePaginator
    {
        $term = mb_trim($this->search);

        $page = Application::query()
            ->withStageSince()
            ->where('applications.team_id', $this->teamId)
            ->where('applications.requisition_id', $this->requisitionId)
            ->with([
                'candidate.media',
                'candidate.address',
                'candidate.skills',
                'candidate.degrees',
                'candidate.workExperiences',
                'candidate.user.links',
                'currentStage',
                'teamView.viewer',
                'stageHistory' => fn ($history) => $history->latest()->limit(1)->with(['toStage', 'movedBy']),
                'evaluations',
                'screeningResponses.question',
            ])
            ->withListingCounts()
            ->when($term !== '', fn (Builder $query) => $query->searchCandidate($term))
            ->when($this->stageId !== '', fn (Builder $query) => $query->where('applications.current_stage_id', $this->stageId))
            ->when($this->statusGroup instanceof ApplicationStatusGroup, fn (Builder $query) => $query->inStatusGroup($this->statusGroup))
            ->withScreeningVerdict($this->screening)
            ->withSeenState($this->seen)
            ->orderForListing($this->sort)
            ->paginate(self::APPLICATIONS_PER_PAGE);

        foreach ($page->items() as $application) {
            $application->candidate?->user->setRelation('candidate', $application->candidate);
        }

        return $page;
    }

    #[Computed]
    public function selectedApplication(): ?Application
    {
        if ($this->selectedId === '') {
            return null;
        }

        return collect($this->applicationsPage->items())->first(fn (Application $application): bool => (string) $application->getKey() === $this->selectedId);
    }

    /**
     * @return EloquentCollection<int, JobRequisition>
     */
    #[Computed]
    public function requisitionResults(): EloquentCollection
    {
        $term = mb_trim($this->requisitionSearch);

        return JobRequisition::query()
            ->where('team_id', $this->teamId)
            ->with('post')
            ->withRecruiterOverviewCounts()
            ->when($this->onlyPublished, fn (Builder $query) => $query->where('status', RequisitionStatusEnum::Published->value))
            ->when($term !== '', fn (Builder $query) => $query->searchTitle($term))
            ->orderForRecruiterOverview(RequisitionOverviewSort::New)
            ->limit(8)
            ->get();
    }

    public function viewUrl(Application $application): string
    {
        return ApplicationResource::getUrl('view', ['record' => $application]);
    }

    public function openRequisition(string $requisitionId): void
    {
        $this->requisitionId = $requisitionId;
        $this->requisitionSearch = '';
        $this->selectedId = '';
        $this->reset('search', 'stageId', 'statusGroup', 'screening', 'seen', 'sort');
        $this->resetPage();
    }

    public function closeRequisition(): void
    {
        $this->requisitionId = '';
        $this->requisitionSearch = '';
        $this->selectedId = '';
        $this->resetPage();
    }

    public function updatedRequisitionSearch(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedRequisitionSort(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedOnlyPublished(): void
    {
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function sortRequisitions(string $sort): void
    {
        $this->requisitionSort = RequisitionOverviewSort::tryFrom($sort) ?? RequisitionOverviewSort::New;
        $this->resetPage(self::REQUISITIONS_PAGE_NAME);
    }

    public function filterStage(string $stageId): void
    {
        $stageId = Str::isUuid($stageId) ? $stageId : '';

        $this->stageId = $this->stageId === $stageId ? '' : $stageId;
        $this->resetPage();
    }

    public function filterStatusGroup(string $group): void
    {
        $this->statusGroup = ApplicationStatusGroup::tryFrom($group);
        $this->resetPage();
    }

    public function filterScreening(string $filter): void
    {
        $this->screening = ScreeningVerdictFilter::tryFrom($filter) ?? ScreeningVerdictFilter::All;
        $this->resetPage();
    }

    public function filterSeen(string $filter): void
    {
        $this->seen = SeenFilter::tryFrom($filter) ?? SeenFilter::All;
        $this->resetPage();
    }

    public function sortBy(string $sort): void
    {
        $this->sort = ApplicationListSort::tryFrom($sort) ?? ApplicationListSort::Attention;
        $this->resetPage();
    }

    public function select(string $applicationId): void
    {
        $this->selectedId = $applicationId;
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'stageId', 'statusGroup', 'screening', 'seen');
        $this->resetPage();
    }

    public function render(): View
    {
        return view('panel-organization::livewire.applications.workspace');
    }
}
