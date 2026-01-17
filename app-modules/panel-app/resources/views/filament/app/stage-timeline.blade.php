<div>
    @forelse ($history as $item)
        <div class="mb-6 border-b border-gray-200 pb-6 last:border-0">
            <div class="flex gap-4">
                <div class="shrink-0">
                    <div class="mt-1.5 h-3 w-3 rounded-full bg-blue-500"></div>
                </div>
                <div class="flex-1">
                    <div>
                        <p class="font-medium text-gray-900">
                            {{ $item->fromStage->name ?? __('panel-app::filament.stage_timeline.application') }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $item->created_at->format('M j, Y') }}
                        </p>
                    </div>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-gray-400">→</span>
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $item->toStage?->name ?? ($item->notes ?? __('panel-app::filament.stage_timeline.stage')) }}
                            </p>
                            @if ($item->movedBy)
                                <p class="text-xs text-gray-500">
                                    {{ __('panel-app::filament.stage_timeline.by') }} {{ $item->movedBy->name }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="py-4 text-center text-gray-500">{{ __('panel-app::filament.stage_timeline.empty') }}</p>
    @endforelse
</div>
