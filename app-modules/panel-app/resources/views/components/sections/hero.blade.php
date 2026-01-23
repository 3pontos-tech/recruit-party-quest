@php
    $usersImages = [
        'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-1.2.1&auto=format&fit=crop&w=256&q=80',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&auto=format&fit=crop&w=256&q=80',
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&auto=format&fit=crop&w=256&q=80',
        'https://images.unsplash.com/photo-1527980965255-d3b416303d12?ixlib=rb-1.2.1&auto=format&fit=crop&w=256&q=80',
        'https://images.unsplash.com/photo-1527980965255-d3b416303d12?ixlib=rb-1.2.1&auto=format&fit=crop&w=256&q=80',
    ];
@endphp

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
                    <div class="flex flex-col gap-2 sm:items-start">
                        <x-he4rt::heading class="text-2xl">Confira todas as nossas vagas</x-he4rt::heading>
                        <x-he4rt::text>12 vagas disponíveis</x-he4rt::text>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-he4rt::button icon="heroicon-o-funnel">Filtrar</x-he4rt::button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <x-he4rt::card class="group">
                        <x-slot:header class="gap-4">
                            <div
                                class="bg-elevation-01dp border-outline-dark flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border"
                            >
                                <x-he4rt::icon
                                    size="lg"
                                    icon="heroicon-o-briefcase"
                                    class="text-text-medium group-hover:text-text-high transition duration-500"
                                />
                            </div>
                            <div class="flex flex-1 flex-col gap-0.5">
                                <x-he4rt::heading class="text-2xl" size="sm" :level="2">
                                    Consultor financeiro
                                </x-he4rt::heading>
                                <x-he4rt::text class="group-hover:text-text-high transition duration-500">
                                    3 Pontos
                                </x-he4rt::text>
                            </div>
                        </x-slot>

                        <x-slot:tags class="grid grid-cols-4 gap-x-8 gap-y-3">
                            <div class="flex items-center gap-0.5">
                                <x-he4rt::icon
                                    icon="heroicon-o-map-pin"
                                    size="sm"
                                    class="text-icon-medium group-hover:text-text-high transition duration-500"
                                />
                                <x-he4rt::text size="sm" class="group-hover:text-text-high transition duration-500">
                                    Remote
                                </x-he4rt::text>
                            </div>
                        </x-slot>

                        <x-slot:footer>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <x-he4rt::icon
                                        icon="heroicon-o-user"
                                        size="sm"
                                        class="text-icon-medium group-hover:text-text-high transition duration-500"
                                    />
                                    <x-he4rt::text size="sm" class="group-hover:text-text-high transition duration-500">
                                        100 aplicações
                                    </x-he4rt::text>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-he4rt::icon
                                        icon="heroicon-o-clock"
                                        size="sm"
                                        class="text-icon-medium group-hover:text-text-high transition duration-500"
                                    />
                                    <x-he4rt::text size="sm" class="group-hover:text-text-high transition duration-500">
                                        06/01/2026
                                    </x-he4rt::text>
                                </div>
                            </div>
                        </x-slot>
                    </x-he4rt::card>
                </div>
            </div>
        </div>
    </div>
</section>
