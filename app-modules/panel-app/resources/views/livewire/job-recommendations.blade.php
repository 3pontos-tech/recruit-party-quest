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

            <div class="flex flex-col gap-16">
                <div class="flex flex-col items-center gap-8 sm:flex-row sm:justify-between">
                    <div class="flex flex-col gap-4 sm:items-start">
                        <x-he4rt::heading size="2xl">Confira todas as nossas vagas</x-he4rt::heading>
                        <x-he4rt::text>12 vagas disponíveis</x-he4rt::text>
                    </div>

                    <div class="flex items-center gap-8">
                        <x-he4rt::button icon="heroicon-o-funnel">Filtrar</x-he4rt::button>
                        <x-he4rt::button icon:trailing="heroicon-o-chevron-down" variant="outline">
                            Tipo de vaga
                        </x-he4rt::button>
                        <x-he4rt::button icon:trailing="heroicon-o-chevron-down" variant="outline">
                            Modelo de trabalho
                        </x-he4rt::button>
                        <x-he4rt::button icon:trailing="heroicon-o-chevron-down" variant="outline">
                            Local de trabalho
                        </x-he4rt::button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <x-he4rt::card class="group">
                        <x-slot:header class="gap-4">
                            <div
                                class="bg-elevation-01dp border-outline-dark group-hover:bg-elevation-05dp flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border transition duration-500"
                            >
                                <x-he4rt::icon
                                    size="lg"
                                    icon="heroicon-o-briefcase"
                                    class="text-text-medium group-hover:text-text-high transition duration-500"
                                />
                            </div>
                            <div class="flex flex-1 flex-col gap-0.5">
                                <x-he4rt::heading size="2xl" :level="2">Consultor financeiro</x-he4rt::heading>
                                <x-he4rt::text class="group-hover:text-text-high transition duration-500">
                                    3 Pontos
                                </x-he4rt::text>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-he4rt::button icon="heroicon-o-bookmark" variant="outline" size="sm" />
                                <x-he4rt::button icon="heroicon-o-share" variant="outline" size="sm" />
                            </div>
                        </x-slot>

                        <x-slot:tags class="grid grid-cols-3 gap-x-8 gap-y-3">
                            <x-he4rt::tag
                                icon="heroicon-o-map-pin"
                                variant="ghost"
                                class="group-hover:text-text-high transition duration-500"
                            >
                                Remote
                            </x-he4rt::tag>
                        </x-slot>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <x-he4rt::tag
                                    icon="heroicon-o-user"
                                    variant="ghost"
                                    class="group-hover:text-text-high gap-2 transition duration-500"
                                >
                                    100 aplicações
                                </x-he4rt::tag>

                                <x-he4rt::tag
                                    icon="heroicon-o-clock"
                                    variant="ghost"
                                    class="group-hover:text-text-high gap-2 transition duration-500"
                                >
                                    06/01/2026
                                </x-he4rt::tag>
                            </div>
                        </x-slot>
                    </x-he4rt::card>
                </div>
            </div>
        </div>
    </div>
</section>
