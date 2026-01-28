@props([
    'team' => $this->record?->requisition?->team ?? null,
])

<x-he4rt::card
    variant="solid"
    density="normal"
    :interactive="false"
    class="bg-elevation-01dp border-outline-light dark:border-outline-dark"
>
    <div class="flex flex-col gap-4">
        {{-- Header Section --}}
        <div class="flex flex-col gap-2">
            <x-he4rt::heading level="3" size="md" class="text-text-high">
                Get to know {{ $team->name }}
            </x-he4rt::heading>
            <x-he4rt::text size="sm" class="text-text-medium leading-relaxed">
                {{ $team->about }}
            </x-he4rt::text>
        </div>

        @php
            $links = $team->links;
        @endphp

        @if ($links->isNotEmpty())
            {{-- Divider --}}
            <div class="border-outline-low my-2 w-full border-t"></div>

            {{-- Links List --}}
            <div class="flex flex-col gap-1">
                @foreach ($links as $link)
                    <a
                        href="{{ $link->url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group hover:bg-elevation-02dp flex items-center justify-between rounded-lg p-3 transition-colors duration-200"
                    >
                        <div class="flex items-center gap-3">
                            <div class="text-icon-medium transition-colors">
                                <x-he4rt::icon :icon="$link->icon" />
                            </div>
                            <x-he4rt::text size="sm" class="text-text-high font-medium transition-colors">
                                {{ $link->name }}
                            </x-he4rt::text>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="text-text-low group-hover:text-text-medium text-xs font-semibold transition-colors"
                            >
                                Access
                            </span>
                            <x-he4rt::icon icon="heroicon-m-chevron-right" size="sm" class="text-icon-low" />
                        </div>
                    </a>
                    @if (! $loop->last)
                        <div class="border-outline-low/30 mx-3 border-t"></div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</x-he4rt::card>
