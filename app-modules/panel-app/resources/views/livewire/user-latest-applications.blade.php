<div>
    <div class="mb-4 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="flex flex-col gap-4">
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

    <div class="no-scrollbar mb-4 flex items-center gap-2 overflow-x-auto pb-1">
        <x-he4rt::button
            variant="{{ $statusFilter === null ? 'solid' : 'outline' }}"
            size="xs"
            wire:click="filterByStatus(null)"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.all') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'in_review' ? 'solid' : 'outline' }}"
            size="xs"
            wire:click="filterByStatus('in_review')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.in_review') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'interview' ? 'solid' : 'outline' }}"
            size="xs"
            wire:click="filterByStatus('interview')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.interview') }}
        </x-he4rt::button>
        <x-he4rt::button
            variant="{{ $statusFilter === 'offer' ? 'solid' : 'outline' }}"
            size="xs"
            wire:click="filterByStatus('offer')"
        >
            {{ __('panel-app::livewire/user-latest-applications.filters.offer') }}
        </x-he4rt::button>
    </div>

    <div class="flex flex-col gap-8" wire:transition>
        @forelse ($this->applications as $application)
            <x-panel-app::jobs.job-card :job="$application->requisition">
                <x-slot:footer>
                    @php
                        $visibleStages = $application->requisition->stages
                            ->where('hidden', false)
                            ->sortBy('display_order')
                            ->values();
                        $totalStages = $visibleStages->count();
                        $currentStageOrder = $application->currentStage?->display_order ?? 0;
                        $currentStageIndex = $visibleStages->search(fn ($s) => $s->id === $application->current_stage_id);
                        $currentPosition = $currentStageIndex !== false ? $currentStageIndex + 1 : 1;
                    @endphp

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-he4rt::icon
                                icon="fas-spinner"
                                @class(['bg-transparent! group-hover:scale-110 group-hover:rotate-360 transition duration-700', $this->getStatusColor($application->status)])
                            />
                            <x-he4rt::text
                                size="sm"
                                @class(['bg-transparent!', $this->getStatusColor($application->status)])
                            >
                                {{ __('panel-app::livewire/user-latest-applications.application_card.stage', ['current' => $currentPosition, 'total' => $totalStages]) }}
                                {{ $application->status->getLabel() }}
                            </x-he4rt::text>
                        </div>

                        <div class="flex items-center gap-1">
                            @foreach ($visibleStages as $index => $stage)
                                <div
                                    @class([
                                        'h-1 w-8 rounded-full',
                                        'group-hover:animate-pulse' => $index < $currentPosition,
                                        $this->getStatusBarColor($application->status) => $index < $currentPosition,
                                        'border-outline-light dark:border-outline-dark bg-elevation-02dp border' => $index >= $currentPosition,
                                    ])
                                ></div>
                            @endforeach
                        </div>
                    </div>
                </x-slot>
            </x-panel-app::jobs.job-card>
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
