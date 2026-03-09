@php
    use He4rt\Applications\Enums\ApplicationStatusEnum;
@endphp

<div>
    <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="flex flex-col gap-2">
            <x-he4rt::heading level="2" size="sm">
                {{ __('panel-app::livewire/user-latest-applications.title') }}
            </x-he4rt::heading>
            <x-he4rt::text size="sm" class="text-text-medium">
                {{ __('panel-app::livewire/user-latest-applications.subtitle') }}
            </x-he4rt::text>
        </div>
        <div class="flex items-center gap-2">
            <x-he4rt::input
                type="text"
                wire:model.live="search"
                placeholder="{{ __('panel-app::livewire/user-latest-applications.search.placeholder') }}"
                class="text-sm"
            />
        </div>
    </div>

    <div class="no-scrollbar mb-8 flex items-center gap-2 overflow-x-auto pb-1">
        <x-he4rt::button
            variant="{{ $statusFilter === null ? 'solid' : 'outline' }}"
            size="sm"
            wire:click="filterByStatus(null)"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.all') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'in_review' ? 'solid' : 'outline' }}"
            size="sm"
            wire:click="filterByStatus('in_review')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.in_review') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'interview' ? 'solid' : 'outline' }}"
            size="sm"
            wire:click="filterByStatus('interview')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.interview') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'offer' ? 'solid' : 'outline' }}"
            size="sm"
            wire:click="filterByStatus('offer')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.offer') }}
        </x-he4rt::button>
    </div>

    <div class="flex flex-col gap-8" wire:transition>
        @forelse ($this->applications as $application)
            @php
                $isRejected = $application->status === ApplicationStatusEnum::Rejected;
                $isWithdrawn = $application->status === ApplicationStatusEnum::Withdrawn;

                $visibleStages = $application->requisition->stages
                    ->where('hidden', false)
                    ->sortBy('display_order')
                    ->values();
                $totalStages = $visibleStages->count();
                $currentStage = $application->currentStage;

                if ($totalStages > 0 && ! $isRejected && ! $isWithdrawn) {
                    if ($currentStage && ! $currentStage->hidden) {
                        $idx = $visibleStages->search(fn ($s) => $s->id === $currentStage->id);
                        $currentPosition = $idx + 1;
                        $stageName = $currentStage->name;
                        $stageColor = $currentStage;
                    } else {
                        $currentDisplayOrder = $currentStage?->display_order ?? 0;
                        $lastVisibleStage = $visibleStages->filter(fn ($s) => $s->display_order <= $currentDisplayOrder)->last();
                        if ($lastVisibleStage) {
                            $idx = $visibleStages->search(fn ($s) => $s->id === $lastVisibleStage->id);
                            $currentPosition = $idx + 1;
                            $stageName = $lastVisibleStage->name;
                            $stageColor = $lastVisibleStage;
                        } else {
                            $currentPosition = 0;
                            $stageName = $visibleStages->first()->name;
                            $stageColor = null;
                        }
                    }
                } else {
                    $currentPosition = 0;
                    $stageName = null;
                    $stageColor = null;
                }
            @endphp

            <div @class(['opacity-60' => $isRejected || $isWithdrawn])>
                <x-panel-app::jobs.job-card :job="$application->requisition">
                    <x-slot:footer>
                        <div
                            class="flex flex-col items-start justify-start gap-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            @if ($isRejected || $isWithdrawn || $totalStages === 0)
                                <div class="flex items-center gap-2">
                                    <x-he4rt::icon
                                        :icon="$application->status->getIcon()"
                                        @class([$application->status->getTailwindBadgeClass()])
                                    />
                                    <x-he4rt::text
                                        size="sm"
                                        @class(['font-semibold', $application->status->getTailwindBadgeClass()])
                                    >
                                        {{ $application->status->getLabel() }}
                                    </x-he4rt::text>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <x-he4rt::icon
                                        icon="fas-spinner"
                                        @class(['bg-transparent! group-hover:scale-110 group-hover:rotate-360 transition duration-1000', $stageColor?->stage_type->getBadgeClasses() ?? 'bg-gray-500/10 text-gray-500'])
                                    />
                                    <x-he4rt::text
                                        size="sm"
                                        @class(['bg-transparent! font-semibold', $stageColor?->stage_type->getBadgeClasses() ?? 'bg-gray-500/10 text-gray-500'])
                                    >
                                        {{ __('panel-app::livewire/user-latest-applications.application_card.stage', ['current' => $currentPosition, 'total' => $totalStages]) }}
                                        {{ $stageName }}
                                    </x-he4rt::text>
                                </div>

                                <div class="flex items-center gap-2">
                                    @foreach ($visibleStages as $index => $stage)
                                        <div
                                            @class([
                                                'h-1 w-12 rounded-full',
                                                'group-hover:animate-pulse' => $index < $currentPosition,
                                                $stageColor?->stage_type->getTailwindColorClass() ?? 'bg-gray-500' => $index < $currentPosition,
                                                'border-outline-light dark:border-outline-dark bg-elevation-02dp border' => $index >= $currentPosition,
                                            ])
                                        ></div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-slot>
                </x-panel-app::jobs.job-card>
            </div>
        @empty
            <div class="py-8 text-center">
                <x-he4rt::text size="sm" class="text-text-medium">
                    {{ __('panel-app::livewire/user-latest-applications.empty_state.message') }}
                </x-he4rt::text>
            </div>
        @endforelse
    </div>

    @if ($this->applications->hasPages())
        <div class="mt-4">
            {{ $this->applications->links() }}
        </div>
    @endif
</div>
