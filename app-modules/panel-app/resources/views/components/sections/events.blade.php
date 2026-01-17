<section
    class="hp-section bg-elevation-01dp border-outline-dark relative min-h-0 scroll-mt-30 overflow-hidden border-t border-b"
    id="events"
>
    <div class="absolute right-0 bottom-0 z-1 h-1/2 w-1/2 translate-x-1/2 lg:-translate-y-[110%]">
        <img
            src="{{ asset('images/3pontos/3pontos-ball.svg') }}"
            alt=""
            loading="lazy"
            class="h-auto w-full object-cover"
        />
    </div>

    <div class="hp-container relative z-10">
        <div>
            <x-he4rt::headline :keywords="['3', 'Pontos']">
                <x-slot:badge>
                    <x-he4rt::section-title size="lg">Eventos</x-he4rt::section-title>
                </x-slot>
                <x-slot:title>O Próximo Nível de Colaboração.</x-slot>
                <x-slot:description>
                    Nossos eventos vão além das competições: são espaços de colaboração e inovação onde a comunidade se
                    une para resolver desafios reais, criar conexões valiosas e gerar soluções que transformam negócios
                    e a sociedade. Um lugar para mostrar seu potencial e fazer parte de algo com propósito.
                </x-slot>
                <x-slot:actions>
                    <x-he4rt::button href="#contact">Entre em contato</x-he4rt::button>
                </x-slot>
            </x-he4rt::headline>
        </div>
    </div>
</section>
