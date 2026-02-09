<aside class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-he4rt::icon size="xs" :icon="\Filament\Support\Icons\Heroicon::Funnel" class="text-muted-foreground" />
            <x-he4rt::heading size="xs" level="2">
                {{ __('panel-app::filament.pages.filters.heading') }}
            </x-he4rt::heading>
        </div>
        <x-he4rt::button wire:click="clearFilters" variant="outline" size="sm">
            {{ __('panel-app::filament.pages.filters.clear') }}
        </x-he4rt::button>
    </div>

    {{ $slot }}
</aside>
