<section class="hp-section relative" id="hero">
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
