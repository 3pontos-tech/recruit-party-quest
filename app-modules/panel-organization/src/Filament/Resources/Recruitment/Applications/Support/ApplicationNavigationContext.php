<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Support;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use Illuminate\Support\Collection;

final readonly class ApplicationNavigationContext
{
    /**
     * @param  Collection<int, Application>  $allActive
     */
    private function __construct(
        public Application $current,
        public ?Application $previous,
        public ?Application $next,
        public ?int $position,
        public int $total,
        public Collection $allActive,
    ) {}

    public static function forApplication(Application $current): self
    {
        /** @var Collection<int, Application> $allActive */
        $allActive = Application::query()
            ->where('requisition_id', $current->requisition_id)
            ->whereNotIn('status', [
                ApplicationStatusEnum::Rejected,
                ApplicationStatusEnum::Withdrawn,
            ])
            ->with(['candidate.user', 'currentStage'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $index = $allActive->search(fn (Application $app): bool => $app->id === $current->id);

        if ($index === false) {
            return new self(
                current: $current,
                previous: null,
                next: null,
                position: null,
                total: $allActive->count(),
                allActive: $allActive,
            );
        }

        return new self(
            current: $current,
            previous: $index > 0 ? $allActive->get($index - 1) : null,
            next: $index < $allActive->count() - 1 ? $allActive->get($index + 1) : null,
            position: $index + 1,
            total: $allActive->count(),
            allActive: $allActive,
        );
    }

    public function shouldRender(): bool
    {
        return $this->position !== null && $this->total > 1;
    }
}
