<x-filament-panels::page class="relative" full-height="true">
    <x-panel-app::sections.job-recommendations />

    {{-- <div class="absolute bottom-[5%] z-0 translate-x-[90%] lg:-translate-x-[60%] lg:translate-y-1/3"> --}}
    {{-- <img --}}
    {{-- src="{{ asset('images/3pontos/logo-creation.webp') }}" --}}
    {{-- loading="lazy" --}}
    {{-- class="max-h-125 lg:max-h-175" --}}
    {{-- alt="" --}}
    {{-- /> --}}
    {{-- </div> --}}

    <x-panel-app::sections.job-categories-offers />

    <x-panel-app::sections.job-application-steps />

    <x-panel-app::sections.contact />

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
</x-filament-panels::page>
