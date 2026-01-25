@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
    use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
    use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
@endphp

<div class="bg-background min-h-screen">
    <x-panel-app::jobs.header :jobsCount="$this->jobs->total()" />

    <div class="hp-container py-6">
        <div class="flex gap-6">
            <aside class="hidden w-64 shrink-0 lg:block">
                <div class="sticky top-6">
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
                    </x-panel-app::jobs.filters>
                </div>
            </aside>
            <main class="min-w-0 flex-1">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-he4rt::text size="sm" class="text-muted-foreground">
                            <span class="text-foreground font-semibold">{{ $this->jobs->total() }}</span>
                            jobs found
                        </x-he4rt::text>
                    </div>
                </div>

                <div class="flex flex-col space-y-4">
                    @forelse ($this->jobs as $job)
                        <x-panel-app::jobs.job-card :job="$job" wire:key="job-{{ $job->id }}" />
                    @empty
                        <x-he4rt::card class="flex flex-col items-center justify-center p-12 text-center">
                            <div
                                class="bg-primary/10 mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full"
                            >
                                <x-he4rt::icon size="sm" :icon="Heroicon::MagnifyingGlass" class="text-primary" />
                            </div>
                            <x-he4rt::heading size="sm">No jobs found</x-he4rt::heading>
                            <x-he4rt::text size="sm" class="text-muted-foreground mt-1">
                                Try adjusting your search or filters to find what you're looking for.
                            </x-he4rt::text>
                            <x-he4rt::button wire:click="$set('search', '')" variant="outline" size="sm" class="mt-4">
                                Clear all filters
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
