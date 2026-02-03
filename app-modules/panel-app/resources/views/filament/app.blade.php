<filament::page class="relative" full-height="true">
    <div class="pointer-events-none absolute top-0 left-0 max-w-7xl">
        <img
            src="{{ asset('images/3pontos/hourglass.svg') }}"
            alt=""
            class="h-auto w-full -translate-x-1/3 -translate-y-1/3"
        />
    </div>

    <section class="relative overflow-hidden py-6 md:py-10">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <x-he4rt::text class="mb-1">Welcome back</x-he4rt::text>
                    <x-he4rt::heading size="xl">
                        {{ auth()->user()->name }}
                    </x-he4rt::heading>
                </div>
                <div class="flex flex-col gap-4 sm:flex-row">
                    <x-he4rt::button variant="outline" size="sm" icon="heroicon-o-document-text">
                        Update Resume
                    </x-he4rt::button>
                    <x-he4rt::button
                        variant="solid"
                        size="sm"
                        icon="heroicon-o-sparkles"
                        icon:trailing="heroicon-o-arrow-right"
                    >
                        AI Career Assistant
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
</filament::page>
