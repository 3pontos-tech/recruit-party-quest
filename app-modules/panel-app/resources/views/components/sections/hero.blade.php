<section class="hp-section relative" id="hero">
    <div class="text-outline-light dark:text-outline-dark absolute top-0 left-0">
        <div class="h-auto w-full -translate-x-1/3 -translate-y-1/3">
            @include('partials.hourglass')
        </div>
    </div>
    <div class="hp-container z-10">
        <div class="grid grid-cols-1">
            <div class="my-32 flex flex-col gap-4">
                <x-he4rt::headline align="center" size="2xl">
                    <x-slot:title class="max-w-5xl">
                        Construa sua carreira com a 3 pontos
                    </x-slot>

                    <x-slot:description>
                        Explore oportunidades em diferentes áreas, modelos de trabalho e níveis de experiência
                    </x-slot>
                </x-he4rt::headline>
            </div>

            <livewire:job-recommendations />
        </div>
    </div>
</section>
