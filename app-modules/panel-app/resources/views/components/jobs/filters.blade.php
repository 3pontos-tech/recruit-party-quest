<aside class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-he4rt::icon size="xs" :icon="\Filament\Support\Icons\Heroicon::Funnel" class="text-muted-foreground" />
            <x-he4rt::heading size="xs" level="2">Filters</x-he4rt::heading>
        </div>
    </div>

    {{ $slot }}
</aside>
