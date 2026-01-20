<div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
                <x-he4rt::heading level="2" size="sm" class="mb-1 italic">
                    {{ __('panel-app::livewire/user-latest-applications.title') }}
                </x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-text-medium mt-0.5">
                    {{ __('panel-app::livewire/user-latest-applications.subtitle') }}
                </x-he4rt::text>
            </div>
            <div class="flex items-center gap-2">
                <x-he4rt::input
                    type="text"
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

        <div class="space-y-2">
            @forelse ($this->applications as $application)
                <x-he4rt::card :interactive="true" class="group p-3" density="compact">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div class="flex items-start gap-3">
                            <div
                                class="bg-elevation-02dp flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                            >
                                <x-heroicon-m-photo class="text-icon-medium h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <x-he4rt::heading
                                    level="3"
                                    size="xs"
                                    class="group-hover:text-primary truncate transition-colors"
                                >
                                    {{ $application->requisition->post?->title ?? __('panel-app::livewire/user-latest-applications.application_card.default_title') }}
                                </x-he4rt::heading>
                                <x-he4rt::text size="xs" class="text-text-medium truncate">
                                    {{ $application->requisition->team->name }}
                                </x-he4rt::text>
                                <div
                                    class="text-text-low mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] sm:text-xs"
                                >
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-map-pin class="h-3 w-3" />
                                        São Paulo
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-briefcase class="h-3 w-3" />
                                        Remote
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        {{ __('panel-app::livewire/user-latest-applications.application_card.applied') }}
                                        {{ $application->created_at->format('d de M.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 md:justify-end md:gap-4">
                            <div class="flex flex-col items-start gap-0.5 md:items-end">
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
                    </div>
                    <div class="border-outline-low mt-2 border-t pt-2">
                        <x-he4rt::text size="xs" class="text-text-low text-[10px]">
                            <span class="text-text-high font-medium">
                                {{ __('panel-app::livewire/user-latest-applications.application_card.last_activity') }}
                            </span>
                            {{ $application->stageHistory->first()?->notes ?? __('panel-app::livewire/user-latest-applications.application_card.activity_fallback') }}
                        </x-he4rt::text>
                    </div>
                </x-he4rt::card>
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
</div>
