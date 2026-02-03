<x-filament-widgets::widget>
    <x-he4rt::card :interactive="false" class="p-4" density="compact">
        <x-he4rt::heading level="3" size="xs" class="mb-3">
            {{ __('panel-app::filament.widgets.user_breakdown.title') }}
        </x-he4rt::heading>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($breakdown as $status)
                <div class="bg-elevation-01dp/64 flex items-center gap-2.5 rounded-lg p-2">
                    <x-he4rt::icon :icon="$status['icon']" :class="$status['color']" />
                    <div class="min-w-0">
                        <x-he4rt::text size="sm" class="text-text-high leading-none font-semibold">
                            {{ $status['value'] }}
                        </x-he4rt::text>
                        <x-he4rt::text size="xs" class="text-text-medium truncate">
                            {{ $status['label'] }}
                        </x-he4rt::text>
                    </div>
                </div>
            @endforeach
        </div>
    </x-he4rt::card>
</x-filament-widgets::widget>
