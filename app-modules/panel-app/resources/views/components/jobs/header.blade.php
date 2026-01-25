@props(['jobsCount' => 0])

<div class="border-border bg-card/50 border-outline-light dark:border-outline-dark border-b">
    <div class="py-8">
        <div class="mb-6 flex items-center gap-3">
            <x-he4rt::badge icon="heroicon-o-briefcase" />
            <div class="flex flex-col gap-0.5">
                <x-he4rt::heading size="md" level="1">Search Jobs</x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-muted-foreground">
                    Find your next opportunity from {{ $jobsCount }} open positions
                </x-he4rt::text>
            </div>
        </div>
        <div class="w-full">
            <div class="flex flex-col items-center gap-3 md:flex-row">
                <div class="relative flex-1">
                    <x-he4rt::input
                        wire:model.live.debounce.300ms="search"
                        class="border-border focus:border-primary"
                        placeholder="Job title, keywords, or company"
                        aria-label="Search jobs by title, keywords, or company"
                    />
                </div>

                <x-he4rt::button wire:click="$refresh" :icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass">
                    Search Jobs
                </x-he4rt::button>
            </div>
        </div>
    </div>
</div>
