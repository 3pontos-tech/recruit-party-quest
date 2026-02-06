<x-filament-panels::page>
    <div class="flex flex-col gap-8">
        <div
            class="from-icon-high/20 to-elevation-03dp dark:from-icon-high/6 dark:to-elevation-surface border-outline-light dark:border-outline-dark relative h-60 overflow-hidden rounded-lg border bg-gradient-to-br"
        >
            <div class="flex h-full max-w-2xl flex-col items-center justify-center p-8">
                <x-he4rt::headline size="md">
                    <x-slot:title>3 Pontos</x-slot>
                    <x-slot:description>
                        A 3 Pontos conecta empresas e startups inovadoras a talentos excepcionais, acelerando soluções
                        reais e transformando ideias em impacto.
                    </x-slot>
                </x-he4rt::headline>
            </div>

            <div class="absolute top-0 right-0 hidden translate-x-1/3 lg:block">
                <div class="spin-slow h-auto w-[140%]">
                    @include('partials.logo-rounded')
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_250px]">
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
        </div>
    </div>
</x-filament-panels::page>
