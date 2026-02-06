<x-filament-panels::page>
    <div class="flex gap-8">
        @if (count($this->getSidebarSections()) > 0)
            <aside class="hidden w-64 shrink-0 lg:block">
                <nav class="sticky top-24 space-y-1">
                    @foreach ($this->getSidebarSections() as $sidebarSection)
                        <a
                            href="#{{ $sidebarSection['id'] }}"
                            class="block rounded-lg px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"
                        >
                            {{ $sidebarSection['title'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>
        @endif

        <div class="min-w-0 flex-1 space-y-8">
            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" class="scroll-mt-24">
                    <h2 class="mb-4 text-xl font-semibold text-gray-950 dark:text-white">
                        {{ $section['title'] }}
                    </h2>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! $section['body'] !!}
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
