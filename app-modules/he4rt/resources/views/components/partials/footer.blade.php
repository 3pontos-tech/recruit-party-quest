@props([
    'bg' => 'bg-elevation-02dp',
])

@php
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

<footer class="bg-elevation-surface/32 border-outline-dark relative z-10 border-t backdrop-blur-md">
    <div class="hp-section mb-0! min-h-0! p-0!">
        <div
            class="grid grid-cols-1 content-start items-start gap-8 p-8 sm:gap-10 sm:p-12 lg:grid-cols-2 lg:gap-12 lg:p-16 lg:py-12 xl:grid-cols-7 xl:p-24 xl:py-16"
        >
            <div class="col-span-1 flex flex-col items-start gap-8 lg:col-span-2 lg:gap-12 xl:col-span-2">
                <div class="flex flex-col">
                    <x-he4rt::logo class="mb-0!" />
                </div>
                <div class="flex flex-col gap-y-2 sm:gap-y-3">
                    <x-he4rt::text class="text-text-high font-semibold">Nosso Endereço</x-he4rt::text>
                    <x-he4rt::text size="sm" class="font-medium">
                        R. Gomes de Carvalho, 1629 - sala 105 - Vila Olímpia, São Paulo - SP, 04547-006
                    </x-he4rt::text>
                </div>
            </div>

            <div class="col-span-1">
                <x-he4rt::heading size="2xs" :level="3" class="mb-3 sm:mb-6">Links</x-he4rt::heading>
                <ul class="text-text-medium space-y-2 text-sm sm:space-y-4">
                    <li>
                        <a href="#hero" class="hover:text-secondary transition">Home</a>
                    </li>
                    <li>
                        <a href="#foundations" class="hover:text-secondary transition">Nossos pilares</a>
                    </li>
                    <li>
                        <a href="#projects" class="hover:text-secondary transition">Projetos</a>
                    </li>
                    <li>
                        <a href="#community" class="hover:text-secondary transition">Comunidade</a>
                    </li>
                    <li>
                        <a href="#events" class="hover:text-secondary transition">Eventos</a>
                    </li>
                    <li>
                        <a href="#contact" class="hover:text-secondary transition">Contato</a>
                    </li>
                </ul>
            </div>

            <div class="col-span-1">
                <x-he4rt::heading size="2xs" :level="3" class="mb-3 sm:mb-6">Nossos projetos</x-he4rt::heading>
                <ul class="text-text-medium space-y-2 text-sm sm:space-y-4">
                    <li>
                        <a rel="noopener noreferrer" target="_blank" href="https://firece.com.br/" class="group block">
                            <img
                                src="{{ asset('images/3pontos/partners/firece-logo.webp') }}"
                                alt="firece logo"
                                loading="lazy"
                                class="h-5 w-auto grayscale transition duration-300 group-hover:grayscale-0"
                            />
                        </a>
                    </li>
                    <li>
                        <a
                            rel="noopener noreferrer"
                            target="_blank"
                            href="https://flamma.3pontos.work/"
                            class="group block"
                        >
                            <img
                                src="{{ asset('images/3pontos/partners/flamma-logo.webp') }}"
                                alt="flamma logo"
                                loading="lazy"
                                class="h-5 w-auto grayscale transition duration-300 group-hover:grayscale-0"
                            />
                        </a>
                    </li>
                    <li>
                        <a
                            rel="noopener noreferrer"
                            target="_blank"
                            href="https://ipecapitalbr.com/"
                            class="group block"
                        >
                            <img
                                src="{{ asset('images/3pontos/partners/ipe-logo.webp') }}"
                                alt="ipê capital logo"
                                loading="lazy"
                                class="h-5 w-auto grayscale transition duration-300 group-hover:grayscale-0"
                            />
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-span-1 flex flex-col gap-y-3 sm:gap-y-4">
                <x-he4rt::heading :level="3" size="2xs">Contato</x-he4rt::heading>
                <x-he4rt::text class="font-semibold" size="sm">contato@3pontos.com</x-he4rt::text>
                <div class="flex gap-4">
                    @foreach ($socials as $social)
                        <a
                            target="_blank"
                            rel="noopener noreferrer"
                            :href="$social['link']"
                            class="group cursor-pointer"
                        >
                            <x-he4rt::icon
                                :icon="$social['icon']"
                                class="text-text-medium group-hover:text-text-high transition"
                            />
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="col-span-1 flex flex-col gap-y-3 sm:gap-y-4 xl:col-span-2">
                <x-he4rt::heading :level="3" size="2xs">Nossa Newsletter</x-he4rt::heading>
                <x-he4rt::text size="sm">
                    Envie nos o seu email e receba as melhores notícias e textos sobre o que acontece no mercado
                    financeiro
                </x-he4rt::text>
                <form class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:gap-4">
                    <x-he4rt::input type="email" placeholder="Seu email" id="email" class="flex-1" />
                    <x-he4rt::button type="submit" class="shrink-0">Inscrever-se</x-he4rt::button>
                </form>
            </div>
        </div>
    </div>
</footer>
