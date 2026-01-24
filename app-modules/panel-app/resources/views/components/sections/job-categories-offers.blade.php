<section class="hp-section relative z-10" id="contact">
    <div class="hp-container flex flex-col gap-16">
        <div class="mb-0 grid grid-cols-1 items-start gap-x-16 lg:grid-cols-[1fr_7fr]">
            <div
                x-data="{ visible: false }"
                x-intersect.once="visible = true"
                class="mb-4 flex items-center justify-center sm:justify-start"
            >
                <x-he4rt::animate-block duration="700">
                    <x-he4rt::section-title size="lg">Categorias</x-he4rt::section-title>
                </x-he4rt::animate-block>
            </div>

            <div>
                <x-he4rt::headline class="mx-0">
                    <x-slot:title>Procure por categorias</x-slot>
                    <x-slot:description>
                        Estamos prontos para acelerar seu negócio ou te receber em nossa comunidade. Escolha a forma de
                        contato que funciona melhor para você.
                    </x-slot>
                </x-he4rt::headline>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <x-he4rt::card class="group p-8" density="compact">
                <x-slot:icon class="gap-4">
                    <div
                        class="bg-elevation-01dp border-outline-dark group-hover:bg-elevation-05dp flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border transition duration-500"
                    >
                        <x-he4rt::icon
                            size="lg"
                            icon="heroicon-o-briefcase"
                            class="text-text-medium group-hover:text-text-high transition duration-500"
                        />
                    </div>
                </x-slot>

                <x-slot:title>Financeiro</x-slot>

                <x-slot:description>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ornare fermentum efficitur. Mauris ut
                    nisl malesuada, fermentum diam sed, lacinia purus.
                </x-slot>

                <x-slot:footer class="border-t-0 pt-0">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col gap-1">
                            <x-he4rt::text>Média salárial</x-he4rt::text>
                            <x-he4rt::heading :level="4" size="2xl">R$ 100.000</x-he4rt::heading>
                        </div>
                        <div>
                            <x-he4rt::icon icon="heroicon-o-chevron-right" />
                        </div>
                    </div>
                </x-slot>
            </x-he4rt::card>
        </div>
    </div>
</section>
