@php
    $contacts = [
        [
            'label' => 'Telefone',
            'value' => '+55 (11) 95398-1486',
            'icon' => 'heroicon-o-phone',
        ],
        [
            'label' => 'Email',
            'value' => 'recrutamento@3pontos.com',
            'icon' => 'heroicon-o-envelope',
        ],
        [
            'label' => 'Endereço',
            'value' => 'R. Gomes de Carvalho, 1629 - sala 103 - Vila Olímpia, São Paulo - SP, 04547-006',
            'icon' => 'heroicon-o-map-pin',
        ],
    ];

    $socials = [
        [
            'icon' => 'fab-instagram',
            'link' => 'https://www.instagram.com/3pontos.hub/',
        ],
        [
            'icon' => 'fab-linkedin',
            'link' => 'https://www.linkedin.com/company/3pontos3/',
        ],
        [
            'icon' => 'fab-square-facebook',
            'link' => 'https://www.facebook.com/profile.php?id=61582825820628',
        ],
        [
            'icon' => 'fab-x-twitter',
            'link' => 'https://x.com/3Pontoshub',
        ],
        [
            'icon' => 'fab-github',
            'link' => 'https://github.com/3pontos-tech',
        ],
    ];
@endphp

<section class="hp-section relative z-10" id="contact">
    <div class="hp-container grid grid-cols-1 items-start gap-x-12 lg:grid-cols-[1fr_7fr]">
        <div
            x-data="{ visible: false }"
            x-intersect.once="visible = true"
            class="mb-4 flex items-center justify-center sm:justify-start"
        >
            <x-he4rt::animate-block duration="700">
                <x-he4rt::section-title size="lg">Contato</x-he4rt::section-title>
            </x-he4rt::animate-block>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-20">
            <div class="order-first lg:col-span-2">
                <x-he4rt::headline class="mx-0">
                    <x-slot:title>Fale conosco</x-slot>
                    <x-slot:description>
                        Estamos prontos para acelerar seu negócio ou te receber em nossa comunidade. Escolha a forma de
                        contato que funciona melhor para você.
                    </x-slot>
                </x-he4rt::headline>
            </div>

            <livewire:contact-form />

            <div
                x-data="{ visible: false }"
                x-intersect.threshold.80="visible = true"
                class="order-1 grid h-full grid-cols-1 content-between gap-12 sm:grid-cols-2 lg:order-2 lg:grid-cols-1"
            >
                @foreach ($contacts as $index => $item)
                    <x-he4rt::animate-block type="fade-right" :delay="$index * 100">
                        <x-he4rt::card :interactive="false" class="h-fit border-none bg-transparent p-0">
                            <x-slot:icon class="flex-col items-start gap-3 sm:flex-row sm:items-center">
                                <div
                                    class="bg-elevation-01dp border-outline-light dark:border-outline-dark flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border"
                                >
                                    <x-he4rt::icon size="lg" :icon="$item['icon']" class="bg-elevation-02dp" />
                                </div>
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <x-he4rt::text class="font-medium">
                                        {{ $item['label'] }}
                                    </x-he4rt::text>
                                    <x-he4rt::text size="2xl" class="text-text-high font-bold">
                                        {{ $item['value'] }}
                                    </x-he4rt::text>
                                </div>
                            </x-slot>
                        </x-he4rt::card>
                    </x-he4rt::animate-block>
                @endforeach

                <x-he4rt::animate-block type="fade-right" :delay="count($contacts) * 100">
                    <x-he4rt::card :interactive="false" class="h-fit border-none bg-transparent p-0">
                        <x-slot:icon class="flex-col items-start gap-3 sm:flex-row sm:items-center">
                            <div
                                class="bg-elevation-01dp border-outline-light dark:border-outline-dark flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border"
                            >
                                <x-he4rt::icon size="lg" icon="heroicon-o-arrow-path" class="bg-elevation-02dp" />
                            </div>
                            <div class="mb-1.5 flex flex-1 flex-col gap-2">
                                <x-he4rt::text class="font-medium">Siga nas redes sociais</x-he4rt::text>
                                <div class="flex gap-6">
                                    @foreach ($socials as $social)
                                        <a
                                            class="cursor-pointer"
                                            href="{{ $social['link'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <x-he4rt::icon
                                                :icon="$social['icon']"
                                                class="border-none bg-transparent p-0"
                                            />
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </x-slot>
                    </x-he4rt::card>
                </x-he4rt::animate-block>
            </div>
        </div>
    </div>
</section>
