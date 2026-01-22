<div class="bg-background min-h-screen">
    <x-panel-app::jobs.header :jobsCount="$this->jobs->total()" />

    <div class="hp-container py-6">
        <div class="flex gap-6">
            <aside class="hidden w-64 shrink-0 lg:block">
                <div class="sticky top-6">
                    <x-panel-app::jobs.filters />
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
                    <div class="flex items-center gap-3">
                        <x-he4rt::button
                            variant="outline"
                            size="sm"
                            class="lg:hidden"
                            :iconLeading="\Filament\Support\Icons\Heroicon::AdjustmentsHorizontal"
                        >
                            Filters
                        </x-he4rt::button>
                        <div class="flex items-center gap-2">
                            <x-he4rt::icon
                                size="xs"
                                :icon="\Filament\Support\Icons\Heroicon::ArrowsUpDown"
                                class="text-muted-foreground hidden sm:block"
                            />
                            <x-he4rt::button
                                variant="outline"
                                size="sm"
                                class="w-[160px] justify-between"
                                :iconTrailing="\Filament\Support\Icons\Heroicon::ChevronDown"
                            >
                                Most Relevant
                            </x-he4rt::button>
                        </div>
                        <div class="hidden items-center sm:flex">
                            <x-he4rt::button
                                variant="outline"
                                size="sm"
                                class="rounded-r-none border-r-0"
                                :iconLeading="\Filament\Support\Icons\Heroicon::ListBullet"
                            />
                            <x-he4rt::button
                                variant="outline"
                                size="sm"
                                class="rounded-l-none"
                                :iconLeading="\Filament\Support\Icons\Heroicon::Squares2x2"
                            />
                        </div>
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
                                <x-he4rt::icon
                                    size="sm"
                                    :icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
                                    class="text-primary"
                                />
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
