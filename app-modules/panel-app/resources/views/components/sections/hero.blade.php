<section class="hp-section relative" id="hero">
    <div class="absolute top-0 left-0">
        <img
            src="{{ asset('images/3pontos/hourglass.svg') }}"
            alt=""
            class="h-auto w-full -translate-x-1/3 -translate-y-1/3"
        />
    </div>
    <div class="hp-container z-10">
        <div class="grid grid-cols-1">
            <div class="my-32 flex flex-col gap-4">
                <x-he4rt::headline align="center" size="2xl">
                    <x-slot:title>Construa sua carreira com a 3 pontos</x-slot>

                    <x-slot:description>
                        Explore oportunidades em diferentes áreas, modelos de trabalho e níveis de experiência
                    </x-slot>
                </x-he4rt::headline>
            </div>

            <livewire:job-recommendations />
        </div>
    </div>
</section>
