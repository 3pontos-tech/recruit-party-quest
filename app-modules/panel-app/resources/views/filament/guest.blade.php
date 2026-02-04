<x-filament-panels::page class="relative" full-height="true">
    <x-panel-app::sections.hero />

    <x-panel-app::sections.job-categories-offers />

    <x-panel-app::sections.job-application-steps />

    <x-panel-app::sections.contact />

    <div class="absolute bottom-[5%] z-0 translate-x-[90%] lg:-translate-x-[60%] lg:translate-y-1/3">
        <img
            src="{{ asset('images/3pontos/logo-creation.webp') }}"
            loading="lazy"
            class="max-h-125 lg:max-h-175"
            alt=""
        />
    </div>
</x-filament-panels::page>
