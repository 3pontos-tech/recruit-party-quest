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
                        <x-he4rt::heading :level="2" class="font-family-secondary mb-6 font-normal">
                            {{ $section['title'] }}
                        </x-he4rt::heading>
                        <div class="fi-prose prose-h2:text-red-500 prose-p:text-green-500 max-w-none">
                            {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($section['body']) }}
                        </div>
                    </section>
                @endforeach
            </div>

            @if (count($this->getSidebarSections()) > 0)
                <aside class="hidden w-64 shrink-0 lg:block">
                    <nav class="sticky top-24 space-y-1">
                        <x-he4rt::text class="text-text-light mb-2 font-medium">Nessa página</x-he4rt::text>

                        @foreach ($this->getSidebarSections() as $sidebarSection)
                            <a
                                href="#{{ $sidebarSection['id'] }}"
                                class="text-text-medium hover:text-text-light block rounded-lg px-3 py-2 font-medium transition"
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
