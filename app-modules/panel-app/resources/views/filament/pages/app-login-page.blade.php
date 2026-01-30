@props([
    'heading' => null,
    'subheading' => null,
])

@php
    $heading ??= $this->getHeading();
    $subheading ??= $this->getSubHeading();
    $hasLogo = $this->hasLogo();
@endphp

<div {{ $attributes->class(['fi-simple-page']) }}>
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_START, scopes: $this->getRenderHookScopes()) }}

    <div class="fi-simple-page-content grid-cols-1! gap-x-28! overflow-hidden p-1 lg:grid-cols-2!">
        <div class="self-end lg:pt-110">
            <div
                class="absolute top-0 left-0 hidden shrink-0 -translate-x-1/4 -translate-y-1/4 items-center justify-center lg:flex"
            >
                <img
                    src="{{ asset('images/3pontos/3pontos-ball.png') }}"
                    alt="3 Pontos Criação"
                    class="h-auto w-1/2 max-w-full object-contain lg:w-full"
                />
            </div>

            <div class="relative z-10 flex flex-col gap-4">
                <div class="flex justify-center lg:justify-start">
                    <x-he4rt::logo size="sm" path="{{ asset('images/3pontos/logo-compact.svg') }}" />
                </div>
                <x-he4rt::heading class="hidden lg:block" size="4xl">
                    Construa sua carreira com a 3 pontos
                </x-he4rt::heading>
                <x-he4rt::text class="hidden lg:block">
                    Explore oportunidades em diferentes áreas, modelos de trabalho e níveis de experiência
                </x-he4rt::text>
            </div>
        </div>
        <div class="flex flex-col justify-center gap-8">
            <div class="flex flex-col gap-4 text-center lg:text-start">
                <x-he4rt::heading size="lg">Faça seu login</x-he4rt::heading>
                <x-he4rt::text>
                    Explore oportunidades em diferentes áreas, modelos de trabalho e níveis de experiência
                </x-he4rt::text>
            </div>

            {{ $this->content }}

            <div class="flex flex-col gap-8">
                <x-he4rt::text class="text-center">Voce também pode fazer login utilizando:</x-he4rt::text>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <x-he4rt::button icon="fab-google" variant="outline" class="px-1 font-bold">
                        Entrar com google
                    </x-he4rt::button>

                    <x-he4rt::button icon="fab-github" variant="outline" class="px-1 font-bold">
                        Entrar com Github
                    </x-he4rt::button>

                    <x-he4rt::button icon="fab-linkedin" variant="outline" class="px-1 font-bold">
                        Entrar com LinkedIn
                    </x-he4rt::button>
                </div>

                <div class="flex items-center justify-center gap-1">
                    <x-he4rt::text>Não possui uma conta?</x-he4rt::text>
                    <a class="group" href="{{ route('filament.app.auth.register') }}">
                        <x-he4rt::text
                            class="text-text-high font-bold group-hover:underline group-hover:underline-offset-2"
                        >
                            Crie uma conta agora mesmo
                        </x-he4rt::text>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (! $this instanceof \Filament\Tables\Contracts\HasTable)
        <x-filament-actions::modals />
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_PAGE_END, scopes: $this->getRenderHookScopes()) }}
</div>
