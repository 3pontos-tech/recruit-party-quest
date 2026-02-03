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

    <div class="space-y-2" wire:transition>
        @forelse ($this->applications as $application)
            <x-panel-app::jobs.job-card :job="$application->requisition">
                <x-slot:footer>
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-start gap-0.5">
                            <span
                                @class(['inline-flex w-fit items-center justify-center rounded-md px-1.5 py-0.5 text-[10px] font-medium whitespace-nowrap', $this->getStatusColor($application->status)])
                            >
                                {{ $application->status->getLabel() }}
                            </span>
                            <x-he4rt::text size="xs" class="text-text-low text-[10px]">
                                {{ $application->currentStage?->name ?? __('panel-app::livewire/user-latest-applications.application_card.no_stage') }}
                            </x-he4rt::text>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-he4rt::tag
                                icon="heroicon-o-calendar"
                                variant="ghost"
                                class="group-hover:text-text-high transition duration-500"
                            >
                                {{ __('panel-app::livewire/user-latest-applications.application_card.applied') }}
                                {{ $application->created_at->format('d/m/Y') }}
                            </x-he4rt::tag>
                            <x-he4rt::button
                                variant="outline"
                                size="xs"
                                icon="heroicon-o-chat-bubble-left-right"
                                icon-position="leading"
                            >
                                {{ __('panel-app::livewire/user-latest-applications.application_card.view_job') }}
                            </x-he4rt::button>
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
