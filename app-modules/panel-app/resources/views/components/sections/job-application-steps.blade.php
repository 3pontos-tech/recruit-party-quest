<section class="hp-section relative z-10" id="contact">
    <div class="hp-container grid grid-cols-1 gap-16 gap-x-32 lg:grid-cols-[1fr_3fr]">
        <div class="mb-0 flex shrink-0 items-center justify-center">
            <img
                src="{{ asset('images/3pontos/3pontos-ball.png') }}"
                alt="3 Pontos Criação"
                class="h-auto w-1/2 max-w-full object-contain lg:w-full"
            />
        </div>

        <div class="flex flex-col items-center justify-center gap-16">
            <div class="grid grid-cols-1 gap-x-16 sm:grid-cols-[1fr_7fr]">
                <div
                    x-data="{ visible: false }"
                    x-intersect.once="visible = true"
                    class="mb-4 flex items-center justify-center sm:items-start sm:justify-start"
                >
                    <x-he4rt::animate-block duration="700">
                        <x-he4rt::section-title size="lg">Categorias</x-he4rt::section-title>
                    </x-he4rt::animate-block>
                </div>

                <div>
                    <x-he4rt::headline class="mx-0">
                        <x-slot:title>Estamos aqui para você</x-slot>
                        <x-slot:description>
                            Se você está buscando o próximo passo na sua carreira ou precisa de ajuda para encontrar os
                            melhores talentos para sua empresa, entre em contato conosco. Nossa equipe está pronta para
                            oferecer soluções personalizadas e suporte especializado para atender às suas necessidades.
                        </x-slot>
                    </x-he4rt::headline>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <x-he4rt::card class="group hover:bg-elevation-05dp flex flex-col gap-8 border-0 hover:scale-102">
                    <div class="relative flex h-9 w-9 items-center justify-center">
                        <div class="border-conic-full absolute inset-0 rounded-full"></div>
                        <span class="text-xl font-bold">1</span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-he4rt::heading size="2xl">Envie sua candidatura</x-he4rt::heading>
                        <x-he4rt::text>
                            Explore as vagas disponíveis e candidate-se às que melhor se encaixam no seu perfil.
                        </x-he4rt::text>
                    </div>
                </x-he4rt::card>

                <x-he4rt::card class="group hover:bg-elevation-05dp flex flex-col gap-8 border-0 hover:scale-102">
                    <div class="relative flex h-9 w-9 items-center justify-center">
                        <div class="border-conic-3-4 absolute inset-0 rounded-full"></div>
                        <span class="text-xl font-bold">2</span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-he4rt::heading size="2xl">Análise do perfil</x-he4rt::heading>
                        <x-he4rt::text>
                            Nossa equipe analisa seu perfil e experiências para encontrar as melhores oportunidades.
                        </x-he4rt::text>
                    </div>
                </x-he4rt::card>

                <x-he4rt::card class="group hover:bg-elevation-05dp flex flex-col gap-8 border-0 hover:scale-102">
                    <div class="relative flex h-9 w-9 items-center justify-center">
                        <div class="border-conic-3-4 absolute inset-0 rounded-full"></div>
                        <span class="text-xl font-bold">3</span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-he4rt::heading size="2xl">Entrevista</x-he4rt::heading>
                        <x-he4rt::text>
                            Participe de entrevistas com empresas alinhadas ao seu perfil profissional.
                        </x-he4rt::text>
                    </div>
                </x-he4rt::card>

                <x-he4rt::card class="group hover:bg-elevation-05dp flex flex-col gap-8 border-0 hover:scale-102">
                    <div class="relative flex h-9 w-9 items-center justify-center">
                        <div class="border-conic-3-4 absolute inset-0 rounded-full"></div>
                        <span class="text-xl font-bold">4</span>
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-he4rt::heading size="2xl">Conquiste sua vaga</x-he4rt::heading>
                        <x-he4rt::text>
                            Receba o suporte necessário para fechar sua contratação com sucesso.
                        </x-he4rt::text>
                    </div>
                </x-he4rt::card>
            </div>
        </div>
    </div>
</section>
