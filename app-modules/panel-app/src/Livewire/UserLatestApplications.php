<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
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
class UserLatestApplications extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?string $statusFilter = null;

    public int $perPage = 4;

    #[Computed]
    public function user(): User
    {
        return auth()->user();
    }

    /**
     * @return LengthAwarePaginator<int, Application>
     */
    #[Computed]
    public function applications(): LengthAwarePaginator
    {
        return Application::query()
            ->whereHas('candidate', fn (Builder $q) => $q->where('user_id', auth()->id()))
            ->with(['requisition.post', 'requisition.team', 'currentStage', 'stageHistory' => fn ($q) => $q->latest()->limit(1)])
            ->when($this->search, fn ($q) => $q->whereHas('requisition.post', fn (Builder $sub) => $sub->where('title', 'like', sprintf('%%%s%%', $this->search))
            ))
            ->when($this->statusFilter, fn ($q) => $q->whereIn('status', $this->getStatusesForFilter($this->statusFilter)))->latest()
            ->paginate($this->perPage);
    }

    public function filterByStatus(?string $status): void
    {
        $this->statusFilter = $this->statusFilter === $status ? null : $status;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): Factory|View
    {
        return view('panel-app::livewire.user-latest-applications');
    }

    /**
     * @phpstan-ignore-next-line
     */
    protected function getStatusesForFilter(?string $filter): array
    {
        return match ($filter) {
            'in_review' => [ApplicationStatusEnum::New, ApplicationStatusEnum::InReview],
            'interview' => [ApplicationStatusEnum::InProgress],
            'offer' => [ApplicationStatusEnum::OfferExtended, ApplicationStatusEnum::OfferAccepted, ApplicationStatusEnum::OfferDeclined],
            default => [],
        };
    }

    protected function getStatusColor(ApplicationStatusEnum $status): string
    {
        return match ($status) {
            ApplicationStatusEnum::New, ApplicationStatusEnum::InReview => 'bg-yellow-500/10 text-yellow-500',
            ApplicationStatusEnum::InProgress => 'bg-purple-500/10 text-purple-500',
            ApplicationStatusEnum::OfferExtended, ApplicationStatusEnum::OfferAccepted => 'bg-green-500/10 text-green-500',
            ApplicationStatusEnum::OfferDeclined, ApplicationStatusEnum::Rejected => 'bg-red-500/10 text-red-500',
            ApplicationStatusEnum::Hired => 'bg-blue-500/10 text-blue-500',
            ApplicationStatusEnum::Withdrawn => 'bg-gray-500/10 text-gray-500',
        };
    }
}
