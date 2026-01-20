<filament::page>
    <main class="flex-1 pt-8 sm:pt-16">
        <section class="relative overflow-hidden py-6 md:py-10">
            <div class="absolute inset-0 opacity-20">
                <svg class="h-full w-full" viewBox="0 0 1200 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M0 200 Q 300 100 600 200 T 1200 200"
                        stroke="currentColor"
                        stroke-width="1"
                        class="text-outline-low"
                    ></path>
                    <path
                        d="M0 250 Q 300 150 600 250 T 1200 250"
                        stroke="currentColor"
                        stroke-width="1"
                        class="text-outline-low"
                    ></path>
                    <path
                        d="M0 150 Q 300 50 600 150 T 1200 150"
                        stroke="currentColor"
                        stroke-width="1"
                        class="text-outline-low"
                    ></path>
                </svg>
            </div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <x-he4rt::heading level="1" size="md" class="mb-1">Welcome back, Maria!</x-he4rt::heading>
                        <x-he4rt::text size="sm" class="text-text-medium">
                            Financial Consultant | 5+ years of experience
                        </x-he4rt::text>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-he4rt::button
                            variant="outline"
                            size="sm"
                            icon="heroicon-o-document-text"
                            icon-position="leading"
                        >
                            Update Resume
                        </x-he4rt::button>
                        <x-he4rt::button variant="solid" size="sm" icon="heroicon-o-sparkles" icon-position="leading">
                            AI Career Assistant
                            <x-slot name="trailing">
                                <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                            </x-slot>
                        </x-he4rt::button>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-5">
                    {{ $this->headerWidgets }}
                </div>
            </div>
        </section>

        <section class="py-6">
            <livewire:user-latest-applications />
        </section>
        <x-he4rt::partials.footer
            logoPath="images/3pontos/logo.svg"
            logoSize="sm"
            description="Somos o ecossistema que une solução e conhecimento em um único lugar. Aceleramos sua empresa. Fortalecemos sua carreira."
            company="3 Pontos"
            :columns="[
                'Navegação' => [
                    'Home' => '#',
                    'Missão social' => '#social-action',
                    'Comunidade' => '#community',
                    'Propósito' => '#meet-up',
                    'Palestrantes' => '#speakers',
                    'Lineup' => '#lineup',
                    'Ao vivo' => '#watch-live',
                    'Parceiros' => '#partners',
                    'Saiba mais' => '#about',
                ]
            ]"
        />
    </main>
</filament::page>
