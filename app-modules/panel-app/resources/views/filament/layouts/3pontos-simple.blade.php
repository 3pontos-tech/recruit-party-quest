@php
    use Filament\Support\Enums\Width;

    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= filament()->getSimplePageMaxContentWidth() ?? Width::Large;

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
    'after' => null,
    'heading' => null,
    'subheading' => null,
])

    <div class="fi-simple-layout relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <img
                src="{{ asset('images/3pontos/hourglass.svg') }}"
                alt=""
                class="absolute top-0 left-0 h-auto w-full -translate-x-1/3 -translate-y-1/3"
            />
        </div>

        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <img
                src="{{ asset('images/3pontos/logo-chain.webp') }}"
                alt=""
                class="absolute bottom-0 left-0 h-auto w-full translate-y-1/2"
            />
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        @if (($hasTopbar ?? true) &&filament()->auth()->check())
            <div class="fi-simple-layout-header">
                @if (filament()->hasDatabaseNotifications())
                    @livewire(
                        Filament\Livewire\DatabaseNotifications::class,
                        [
                            'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                            'position' => \Filament\Enums\DatabaseNotificationsPosition::Topbar,
                        ]
                    )
                @endif

                @if (filament()->hasUserMenu())
                    @livewire(Filament\Livewire\SimpleUserMenu::class)
                @endif
            </div>
        @endif

        <div class="fi-simple-main-ctn">
            <main
                @class([
                    'fi-simple-main bg-elevation-surface/32 relative rounded-xl backdrop-blur-md',
                    $maxContentWidth instanceof Width ? "fi-width-{$maxContentWidth->value}" : $maxContentWidth,
                ])
            >
                <div
                    class="from-icon-high/6 to-elevation-surface/32 pointer-events-none absolute inset-0 bg-gradient-to-br"
                ></div>

                <div class="relative">
                    {{ $slot }}
                </div>
            </main>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
