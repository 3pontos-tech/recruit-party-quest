<section
    class="hp-section bg-elevation-01dp/86 border-outline-dark relative max-h-[80vh] min-h-[80vh] scroll-mt-30 overflow-hidden border-t border-b backdrop-blur-md"
    id="projects"
>
    <div
        class="hp-container relative z-10 grid h-full grid-cols-1 gap-x-12 py-8 md:py-8 lg:grid-cols-[1fr_2fr] lg:py-10 xl:py-16"
    >
        <div
            class="relative m-0 hidden h-full items-center justify-center mask-t-from-95% mask-t-to-99% mask-b-from-95% mask-b-to-99% lg:block"
        >
            <div class="absolute inset-0 h-full overflow-hidden">
                <img
                    src="{{ asset('images/3pontos/grids-vertical.svg') }}"
                    alt=""
                    class="h-full w-full object-cover"
                />
            </div>
        </div>

        <div class="flex h-full min-h-0 flex-col gap-4 lg:items-start">
            <div>
                <x-he4rt::headline>
                    <x-slot:badge>
                        <x-he4rt::section-title>Projetos</x-he4rt::section-title>
                    </x-slot>
                    <x-slot:title>Aonde a Disrupção acontece</x-slot>
                    <x-slot:description>
                        Conheça alguns dos projetos que nasceram ou foram acelerados dentro do nosso ecossistema. Aqui,
                        a tecnologia se traduz em resultados reais e impacto no mercado, mostrando a sinergia entre
                        nossa aceleradora e a comunidade de talentos.
                    </x-slot>
                </x-he4rt::headline>
            </div>

            <div
                class="flex h-full flex-1 flex-col items-start gap-8 overflow-y-auto mask-t-from-95% mask-t-to-99% mask-b-from-95% mask-b-to-99% p-1 py-4 lg:pr-8"
            >
                <x-he4rt::card class="h-fit gap-4">
                    <x-slot:icon class="items-center gap-4">
                        <x-he4rt::icon size="sm" class="rounded-sm" />
                        <x-he4rt::heading size="2xs">Ipê</x-he4rt::heading>
                    </x-slot>
                    <x-slot:description class="leading-[1.5]">
                        Uma plataforma de investimentos intuitiva e acessível, focada em democratizar o acesso ao
                        mercado financeiro. Com uma interface amigável e integração segura com APIs, ela torna o
                        processo de investimento simples e seguro para todos.
                    </x-slot>
                </x-he4rt::card>

                <x-he4rt::card class="h-fit gap-4">
                    <x-slot:icon class="items-center gap-4">
                        <x-he4rt::icon size="sm" class="rounded-sm" />
                        <x-he4rt::heading size="2xs">Flamma</x-he4rt::heading>
                    </x-slot>
                    <x-slot:description class="leading-[1.5]">
                        Clube de benefícios exclusivo que vai além, oferecendo consultoria financeira personalizada e
                        acesso a produtos e serviços com condições especiais. É a solução ideal para quem busca otimizar
                        suas finanças e ter acesso a vantagens únicas no mercado.
                    </x-slot>
                </x-he4rt::card>

                <x-he4rt::card class="h-fit gap-4">
                    <x-slot:icon class="items-center gap-4">
                        <x-he4rt::icon size="sm" class="rounded-sm" />
                        <x-he4rt::heading size="2xs">Flare</x-he4rt::heading>
                    </x-slot>
                    <x-slot:description class="leading-[1.5]">
                        serviço digital e ágil que oferece avaliação e diagnóstico financeiro detalhado. Utilizando um
                        algoritmo avançado de análise de dados, ele permite que os clientes recebam um parecer
                        financeiro completo em curto período de tempo.
                    </x-slot>
                </x-he4rt::card>
            </div>
        </div>
    </div>
</section>
