<x-filament-widgets::widget>
    <x-he4rt::card :interactive="false" class="p-4" density="compact">
        <x-he4rt::heading level="3" size="xs" class="mb-3">Application Status Breakdown</x-he4rt::heading>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ([
                    ['label' => 'In Review', 'value' => '5', 'icon' => 'heroicon-o-clock', 'color' => 'text-yellow-500'],
                    ['label' => 'Interview', 'value' => '2', 'icon' => 'heroicon-o-calendar', 'color' => 'text-blue-500'],
                    ['label' => 'Offer', 'value' => '1', 'icon' => 'heroicon-o-check-circle', 'color' => 'text-green-500'],
                    ['label' => 'Rejected', 'value' => '4', 'icon' => 'heroicon-o-x-circle', 'color' => 'text-red-500']
                ]
                as $status)
                <div class="bg-elevation-01dp flex items-center gap-2.5 rounded-lg p-2">
                    <x-he4rt::icon :icon="$status['icon']" @class($status['color']) />
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
