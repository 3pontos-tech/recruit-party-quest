@php
    /**
    * @var \He4rt\Recruitment\Stages\Models\Stage $currentStage
    * @var \Illuminate\Support\Collection<int, \He4rt\Recruitment\Stages\Models\Stage> $stages
    */
@endphp

@props([
    'stages',
    'currentStage',
])

@php
    $stages = $stages->sortBy('display_order');
    $currentStageIndex = $stages->search(fn ($stage) => $stage->id === ($currentStage?->id ?? null));
    if ($currentStageIndex === false) {
        $currentStageIndex = -1;
    }
@endphp

<div class="bg-elevation-01dp border-outline-light dark:border-outline-dark space-y-6 rounded-md border p-6 lg:p-8">
    <div class="flex items-center justify-between">
        <x-he4rt::heading level="3" size="3xs" class="text-text-high">Application Status</x-he4rt::heading>
        @if ($currentStage)
            <x-he4rt::tag variant="solid" size="xs">
                Progresso: {{ $currentStage->display_order }} / {{ $stages->count() }}
            </x-he4rt::tag>
        @endif
    </div>

    <div class="relative">
        <div class="flex flex-col">
            @foreach ($stages as $index => $stage)
                @php
                    $isCompleted = $index < $currentStageIndex;
                    $isCurrent = $index === $currentStageIndex;
                    $isFuture = $index > $currentStageIndex;
                @endphp

                <div @class([
                    'group relative flex items-center gap-6',
                ])>
                    {{-- Timeline Line Segment --}}
                    @if (! $loop->last)
                        <div
                            class="bg-outline-low/30 absolute top-1/2 bottom-0 left-3.5 z-10 h-full w-0.5"
                            aria-hidden="true"
                        ></div>
                    @endif

                    {{-- Indicator Dot --}}
                    <div
                        @class([
                            'relative z-20 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-colors duration-300',
                            'bg-primary border-primary' => $isCompleted || $isCurrent,
                            'bg-elevation-surface border-outline-low' => $isFuture,
                        ])
                    >
                        @if ($isCompleted)
                            <x-he4rt::icon icon="heroicon-m-check" />
                        @elseif ($isCurrent)
                            <div class="h-2.5 w-2.5 animate-pulse rounded-full bg-white"></div>
                        @else
                            <div class="bg-outline-low h-2 w-2 rounded-full"></div>
                        @endif
                    </div>

                    <div class="flex flex-col gap-1 py-4">
                        <x-he4rt::text
                            size="sm"
                            @class([
                                'font-semibold transition-colors duration-300',
                                'text-text-high' => $isCompleted || $isCurrent,
                                'text-text-low' => $isFuture,
                            ])
                        >
                            {{ $stage->name }}
                        </x-he4rt::text>

                        @if ($stage->description)
                            <x-he4rt::text
                                size="xs"
                                @class([
                                    'line-clamp-2 transition-colors duration-300',
                                    'text-text-medium' => $isCompleted || $isCurrent,
                                    'text-text-low/60' => $isFuture,
                                ])
                            >
                                {{ $stage->description }}
                            </x-he4rt::text>
                        @endif

                        @if ($isCurrent)
                            <div class="mt-2 flex items-center gap-2">
                                <x-he4rt::tag variant="solid" size="xs">You are currently at this stage</x-he4rt::tag>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
