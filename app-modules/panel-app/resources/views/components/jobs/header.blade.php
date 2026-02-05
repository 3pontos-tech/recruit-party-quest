@props(['jobsCount' => 0])

<div class="border-border bg-card/50 border-outline-light dark:border-outline-dark border-b">
    <div class="py-8">
        <div class="mb-6 flex items-center gap-3">
            <x-he4rt::badge icon="heroicon-o-briefcase" />
            <div class="flex flex-col gap-0.5">
                <x-he4rt::heading size="md" level="1">
                    {{ __('panel-app::filament.pages.search_jobs.header') }}
                </x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-muted-foreground">
                    {{ __('panel-app::filament.pages.search_jobs.description', ['count' => $jobsCount]) }}
                </x-he4rt::text>
            </div>
        </div>
        <div class="w-full">
            <div class="flex flex-col items-center gap-3 md:flex-row">
                <x-he4rt::input
                    wire:model.live.debounce.300ms="search"
                    class="border-border focus:border-primary w-full"
                    :placeholder="__('panel-app::filament.pages.search_jobs.search_placeholder')"
                    :aria-label="__('panel-app::filament.pages.search_jobs.search_placeholder')"
                />

                <x-he4rt::button
                    class="w-full shrink-0 sm:w-fit"
                    wire:click="$refresh"
                    :icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
                >
                    {{ __('panel-app::filament.pages.search_jobs.search_button') }}
                </x-he4rt::button>
            </div>
        </div>
    </div>
</div>
