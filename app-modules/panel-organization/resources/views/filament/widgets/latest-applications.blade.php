<x-filament-widgets::widget>
    <x-he4rt::card :interactive="false" class="p-4" density="compact">
        <x-he4rt::heading level="3" size="xs" class="mb-3">
            {{ __('panel-organization::filament.widgets.latest_applications.title') }}
        </x-he4rt::heading>

        @if ($applications->isEmpty())
            <x-he4rt::text size="sm" class="text-text-medium">
                {{ __('panel-organization::filament.widgets.latest_applications.empty') }}
            </x-he4rt::text>
        @else
            <div class="flex flex-col gap-2">
                @foreach ($applications as $app)
                    <div
                        class="bg-elevation-01dp/64 flex items-center justify-between gap-3 rounded-lg p-2.5"
                        wire:key="app-{{ $app->id }}"
                    >
                        <div class="min-w-0 flex-1">
                            <x-he4rt::text size="sm" class="text-text-high truncate font-semibold">
                                {{ $app->candidate?->user?->name ?? '—' }}
                            </x-he4rt::text>
                            <x-he4rt::text size="xs" class="text-text-medium truncate">
                                {{ $app->requisition?->post?->title ?? __('panel-organization::filament.widgets.latest_applications.no_position') }}
                            </x-he4rt::text>
                        </div>
                        <div class="shrink-0 text-right">
                            <x-he4rt::text size="xs" class="text-text-medium">
                                {{ $app->currentStage?->stage_type?->getLabel() ?? __('panel-organization::filament.widgets.latest_applications.no_stage') }}
                            </x-he4rt::text>
                            <x-he4rt::text size="xs" class="text-text-low">
                                {{ $app->created_at->diffForHumans() }}
                            </x-he4rt::text>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-he4rt::card>
</x-filament-widgets::widget>
