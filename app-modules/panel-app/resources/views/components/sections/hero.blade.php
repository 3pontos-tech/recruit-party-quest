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
    <div class="absolute inset-0 -left-[20%] z-0 h-full w-[150%]">
        <img
            src="{{ asset('images/3pontos/logo-chain.webp') }}"
            fetchpriority="high"
            alt=""
            class="h-full w-full object-cover"
        />

        <div class="bg-elevation-surface/90 absolute inset-0"></div>
        <div class="from-elevation-surface/80 absolute inset-0 bg-gradient-to-t to-transparent"></div>
    </div>

    <div class="hp-container z-10">
        <div class="grid grid-cols-1">
            <div class="flex flex-col gap-4">
                <x-he4rt::headline align="center" size="2xl">
                    <x-slot:title>
                        3 Pontos: Onde a Tecnologia Encontra o Crescimento e vice versa
                    </x-slot>

                    <x-slot:description>
                        Somos o ecossistema que une solução e conhecimento em um único lugar.
                    </x-slot>
                    <x-slot:actions>
                        <x-he4rt::button href="#contact">Entre em contato</x-he4rt::button>
                    </x-slot>
                </x-he4rt::headline>
            </div>
        </div>
    </div>
</section>
