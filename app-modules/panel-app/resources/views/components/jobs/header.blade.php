@props(['jobsCount' => 0])

<div class="border-border bg-card/50 border-outline-light dark:border-outline-dark border-b">
    <div class="hp-container py-8">
        <div class="mb-6 flex items-center gap-3">
            <div class="bg-primary/20 flex h-10 w-10 items-center justify-center rounded-lg">
                <x-he4rt::icon size="sm" class="text-primary" :icon="\Filament\Support\Icons\Heroicon::Briefcase" />
            </div>
            <div>
                <x-he4rt::heading size="md" level="1">Search Jobs</x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-muted-foreground">
                    Find your next opportunity from {{ $jobsCount }} open positions
                </x-he4rt::text>
            </div>
        </div>
        <div class="w-full">
            <div class="flex flex-col gap-3 md:flex-row">
                <div class="relative flex-1">
                    <x-he4rt::input
                        wire:model.live.debounce.300ms="search"
                        class="border-border focus:border-primary"
                        placeholder="Job title, keywords, or company"
                        aria-label="Search jobs by title, keywords, or company"
                    />
                </div>

                <x-he4rt::button
                    wire:click="$refresh"
                    :iconLeading="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
                >
                    Search Jobs
                </x-he4rt::button>
            </div>
        </div>
    </div>
</div>
