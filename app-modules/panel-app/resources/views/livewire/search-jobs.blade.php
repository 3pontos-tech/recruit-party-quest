@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
    use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
    use He4rt\Recruitment\Requisitions\Enums\JobCategoryEnum;
    use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
@endphp

<div class="hp-section mb-24 min-h-0!" x-data="{ mobileFiltersOpen: false }">
    <div class="hp-container">
        <x-panel-app::jobs.header :jobsCount="$this->jobs->total()" />

        <div class="py-6">
            <div class="flex gap-6">
                <aside class="hidden w-64 shrink-0 lg:block">
                    <div class="sticky top-24">
                        <x-panel-app::jobs.filters>
                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.work_arrangement')"
                                wire:model.live="workArrangements"
                                :items="WorkArrangementEnum::cases()"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.employment_type')"
                                wire:model.live="employmentTypes"
                                :items="EmploymentTypeEnum::cases()"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.experience_level')"
                                wire:model.live="experienceLevel"
                                :items="ExperienceLevelEnum::cases()"
                                type="radio"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.category')"
                                wire:model.live="category"
                                :items="JobCategoryEnum::cases()"
                                type="radio"
                            />
                        </x-panel-app::jobs.filters>
                    </div>
                </aside>

                <div x-show="mobileFiltersOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
                    <div
                        x-show="mobileFiltersOpen"
                        x-transition:enter="transition-opacity duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="bg-elevation-surface/32 fixed inset-0 backdrop-blur-md"
                        @click="mobileFiltersOpen = false"
                    ></div>

                    <div
                        x-show="mobileFiltersOpen"
                        x-transition:enter="transition-transform duration-300"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition-transform duration-300"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="bg-elevation-surface fixed inset-y-0 right-0 flex w-full max-w-sm flex-col overflow-y-auto"
                    >
                        <div
                            class="border-outline-light dark:border-outline-dark flex items-center justify-between border-b px-4 py-4"
                        >
                            <div class="flex items-center gap-2">
                                <x-he4rt::icon size="xs" :icon="Heroicon::Funnel" class="text-muted-foreground" />
                                <x-he4rt::heading size="xs" level="2">
                                    {{ __('panel-app::filament.pages.filters.heading') }}
                                </x-he4rt::heading>
                            </div>
                            <button
                                type="button"
                                @click="mobileFiltersOpen = false"
                                class="text-muted-foreground hover:text-foreground transition-colors"
                            >
                                <x-he4rt::icon size="sm" :icon="Heroicon::XMark" />
                            </button>
                        </div>

                        <div class="flex-1 space-y-4 overflow-y-auto p-4">
                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.work_arrangement')"
                                wire:model.live="workArrangements"
                                :items="WorkArrangementEnum::cases()"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.employment_type')"
                                wire:model.live="employmentTypes"
                                :items="EmploymentTypeEnum::cases()"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.experience_level')"
                                wire:model.live="experienceLevel"
                                :items="ExperienceLevelEnum::cases()"
                                type="radio"
                            />

                            <x-panel-app::jobs.filter
                                :title="__('recruitment::filament.requisition.filters.category')"
                                wire:model.live="category"
                                :items="JobCategoryEnum::cases()"
                                type="radio"
                            />
                        </div>

                        <div class="border-outline-light dark:border-outline-dark flex gap-3 border-t p-4">
                            <x-he4rt::button wire:click="clearFilters" variant="outline" class="flex-1">
                                {{ __('panel-app::filament.pages.filters.clear') }}
                            </x-he4rt::button>
                            <x-he4rt::button @click="mobileFiltersOpen = false" class="flex-1">
                                {{ __('panel-app::filament.pages.filters.apply') }}
                            </x-he4rt::button>
                        </div>
                    </div>
                </div>

                <main class="min-w-0 flex-1">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <x-he4rt::text size="sm" class="text-muted-foreground">
                                <span class="text-foreground font-semibold">{{ $this->jobs->total() }}</span>
                                {{ __('panel-app::filament.pages.search_jobs.jobs_found') }}
                            </x-he4rt::text>
                        </div>

                        <x-he4rt::button
                            @click="mobileFiltersOpen = true"
                            variant="outline"
                            size="sm"
                            class="lg:hidden"
                            :icon="Heroicon::Funnel"
                        >
                            {{ __('panel-app::filament.pages.filters.heading') }}
                        </x-he4rt::button>
                    </div>

                    <div class="flex flex-col space-y-4">
                        @forelse ($this->jobs as $job)
                            <x-panel-app::jobs.job-card :job="$job" wire:key="job-{{ $job->id }}" />
                        @empty
                            <x-he4rt::card
                                :interactive="false"
                                class="flex flex-col items-center justify-center border-dashed p-12 text-center"
                            >
                                <x-he4rt::badge
                                    icon="heroicon-o-magnifying-glass"
                                    class="bg-elevation-05dp rounded-full border-0"
                                />
                                <x-he4rt::heading size="sm">
                                    {{ __('panel-app::filament.pages.search_jobs.no_jobs_found') }}
                                </x-he4rt::heading>
                                <x-he4rt::text size="sm" class="text-muted-foreground mt-1">
                                    {{ __('panel-app::filament.pages.search_jobs.no_jobs_description') }}
                                </x-he4rt::text>
                                <x-he4rt::button wire:click="clearFilters" variant="outline" size="sm" class="mt-4">
                                    {{ __('panel-app::filament.pages.search_jobs.clear_filters') }}
                                </x-he4rt::button>
                            </x-he4rt::card>
                        @endforelse
                    </div>
                    <div class="mt-8">
                        {{ $this->jobs->links() }}
                    </div>
                </main>
            </div>
        </div>
    </div>
</div>
