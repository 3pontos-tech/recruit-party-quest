<x-he4rt::card variant="solid" density="normal" class="bg-elevation-01dp/50 border-outline-low">
    <div class="flex flex-col gap-4">
        {{-- Header Section --}}
        <div class="flex flex-col gap-2">
            <x-he4rt::heading level="3" size="md" class="text-text-high">Get to know 3 Pontos</x-he4rt::heading>
            <x-he4rt::text size="sm" class="text-text-medium leading-relaxed">
                We are the ecosystem that unites solution and knowledge in a single place. We accelerate your company
                while strengthening your career.
            </x-he4rt::text>
        </div>

        {{-- Divider --}}
        <div class="border-outline-low my-2 w-full border-t"></div>

        {{-- Links List --}}
        <div class="flex flex-col gap-1">
            @php
                $links = [
                    ['name' => 'LinkedIn', 'icon' => 'heroicon-o-link', 'url' => 'https://linkedin.com/company/3pontos'],
                    ['name' => 'Instagram', 'icon' => 'heroicon-o-camera', 'url' => 'https://instagram.com/3pontos'],
                    ['name' => 'Website', 'icon' => 'heroicon-o-globe-alt', 'url' => 'https://3pontos.co'],
                ];
            @endphp

            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group hover:bg-elevation-02dp flex items-center justify-between rounded-lg p-3 transition-colors duration-200"
                >
                    <div class="flex items-center gap-3">
                        <div class="text-icon-medium group-hover:text-primary transition-colors">
                            <x-dynamic-component :component="$link['icon']" class="h-5 w-5" />
                        </div>
                        <x-he4rt::text
                            size="sm"
                            class="text-text-high group-hover:text-primary font-medium transition-colors"
                        >
                            {{ $link['name'] }}
                        </x-he4rt::text>
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="text-text-low group-hover:text-text-medium text-xs font-semibold transition-colors"
                        >
                            Access
                        </span>
                        <x-heroicon-m-chevron-right
                            class="text-icon-low group-hover:text-icon-medium h-4 w-4 transition-colors"
                        />
                    </div>
                </a>
                @if (! $loop->last)
                    <div class="border-outline-low/30 mx-3 border-t"></div>
                @endif
            @endforeach
        </div>
    </div>
</x-he4rt::card>
