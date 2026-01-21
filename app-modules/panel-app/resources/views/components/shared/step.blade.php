@props([
    'label',
    'active' => false,
    'completed' => false,
    'icon',
    'animate' => false,
])

<div class="flex flex-col items-center gap-3">
    <div
        @class([
            'flex h-10 w-10 items-center justify-center rounded-full border transition-all duration-500',
            'border-white bg-white text-black shadow-[0_0_15px_rgba(255,255,255,0.3)]' => $completed,
            'scale-110 border-zinc-600 bg-zinc-800 text-white shadow-lg' => $active && ! $completed,
            'border-zinc-800 bg-zinc-900 text-zinc-700' => ! $active && ! $completed,
        ])
    >
        @if ($completed)
            <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::Check" class="h-5 w-5" />
        @else
            <x-filament::icon
                :icon="$icon"
                @class([
                    'w-4 h-4',
                    'animate-spin' => $animate,
                ])
            />
        @endif
    </div>
    <span
        @class([
            'text-[9px] font-black tracking-[0.2em] uppercase transition-colors duration-500',
            'text-white' => $active,
            'text-zinc-700' => ! $active,
        ])
    >
        {{ $label }}
    </span>
</div>
