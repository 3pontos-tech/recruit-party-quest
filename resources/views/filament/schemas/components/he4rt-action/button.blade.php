@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\Size;
    use Illuminate\View\ComponentAttributeBag;

    if (isset($iconPosition) && ! $iconPosition instanceof IconPosition) {
        $iconPosition = filled($iconPosition) ? IconPosition::tryFrom($iconPosition) ?? $iconPosition : null;
    }

    if (isset($size) && ! $size instanceof Size) {
        $size = filled($size) ? Size::tryFrom($size) ?? $size : null;
    }

    if (isset($badgeSize) && ! $badgeSize instanceof Size) {
        $badgeSize = filled($badgeSize) ? Size::tryFrom($badgeSize) ?? $badgeSize : null;
    }

    if (isset($iconSize) && filled($iconSize) && ! $iconSize instanceof IconSize) {
        $iconSize = IconSize::tryFrom($iconSize) ?? $iconSize;
    }

    $iconSize ??= match ($size) {
        Size::ExtraSmall, Size::Small => IconSize::Small,
        default => null,
    };

    $loadingIndicator = $loadingIndicator ?? true;
    $wireTarget = $loadingIndicator
        ? $attributes
            ->whereStartsWith(["wire:target", "wire:click"])
            ->filter(fn ($value): bool => filled($value))
            ->first()
        : null;

    $hasFormProcessingLoadingIndicator = ($type ?? "button") === "submit" && filled($form ?? null);
    $hasLoadingIndicator = filled($wireTarget) || $hasFormProcessingLoadingIndicator;

    if ($hasLoadingIndicator) {
        $loadingIndicatorTarget = html_entity_decode($wireTarget ?: $form, ENT_QUOTES);
    }

    $hasTooltip = filled($tooltip ?? null);
    $isDisabled = $disabled ?? false;

    // Map Filament size to hp-button size
    $hpSize = match ($size) {
        Size::ExtraSmall => "xs",
        Size::Small => "sm",
        Size::Medium => "md",
        Size::Large => "lg",
        Size::ExtraLarge => "lg",
        default => "md",
    };

    $variant = $variant ?? "solid";
    $rounded = $rounded ?? "md";
@endphp

@if ($labeledFrom ?? null)
    {{-- For labeled-from breakpoint, render as icon button on smaller screens --}}
    @include(
        "filament.schemas.components.he4rt-action.icon-button",
        [
            "attributes" => $attributes,
            "badge" => $badge ?? null,
            "badgeColor" => $badgeColor ?? "primary",
            "badgeSize" => $badgeSize ?? Size::ExtraSmall,
            "color" => $color ?? "primary",
            "disabled" => $isDisabled,
            "form" => $form ?? null,
            "formId" => $formId ?? null,
            "href" => $href ?? null,
            "icon" => $icon ?? null,
            "iconSize" => $iconSize,
            "keyBindings" => $keyBindings ?? null,
            "label" => $label ?? "",
            "size" => $size,
            "tag" => $tag ?? "button",
            "target" => $target ?? null,
            "tooltip" => $tooltip ?? null,
            "type" => $type ?? "button",
            "variant" => $variant,
            "rounded" => $rounded,
        ]
    )
@endif

<{{ $tag ?? "button" }}
    @if (($tag ?? "button") === "a" && ! ($isDisabled && $hasTooltip))
        {{ \Filament\Support\generate_href_html($href ?? null, ($target ?? null) === "_blank") }}
    @endif
    @if ($keyBindings ?? null)
        x-bind:id="$id('key-bindings')"
        x-mousetrap.global.{{ collect($keyBindings)->map(fn (string $keyBinding): string => str_replace("+", "-", $keyBinding))->implode(".") }}="document.getElementById($el.id)?.click()"
    @endif
    @if ($hasTooltip)
        x-tooltip="{
            content: @js($tooltip),
            theme: $store.theme,
            allowHTML: @js($tooltip instanceof \Illuminate\Contracts\Support\Htmlable),
        }"
    @endif
    @if ($hasFormProcessingLoadingIndicator)
        x-data="filamentFormButton"
        x-bind:class="{ 'fi-processing': isProcessing }"
    @endif
    {{
        $attributes
            ->merge(
                [
                    "aria-disabled" => $isDisabled ? "true" : null,
                    "aria-label" => $labelSrOnly ?? false ? trim(strip_tags($label ?? "")) : null,
                    "disabled" => $isDisabled && blank($tooltip ?? null),
                    "form" => $formId ?? null,
                    "type" => ($tag ?? "button") === "button" ? $type ?? "button" : null,
                    "wire:loading.attr" => ($tag ?? "button") === "button" ? "disabled" : null,
                    "wire:target" => $hasLoadingIndicator && ($loadingIndicatorTarget ?? null) ? $loadingIndicatorTarget : null,
                    "x-bind:disabled" => $hasFormProcessingLoadingIndicator ? "isProcessing" : null,
                ],
                escape: false,
            )
            ->when(
                $isDisabled && $hasTooltip,
                fn (ComponentAttributeBag $attributes) => $attributes->filter(fn (mixed $value, string $key): bool => ! str($key)->startsWith(["href", "x-on:", "wire:click"])),
            )
            ->class([
                "hp-button",
                "hp-button-" . $variant,
                "hp-button-size-" . $hpSize,
                "hp-button-rounded-" . $rounded,
                "fi-disabled" => $isDisabled,
                "fi-outlined" => $outlined ?? false,
                is_string($labeledFrom ?? null) ? "fi-labeled-from-{$labeledFrom}" : null,
            ])
    }}
>
    @if (($iconPosition ?? IconPosition::Before) === IconPosition::Before)
        @if ($icon ?? null)
            {{
                \Filament\Support\generate_icon_html(
                    $icon,
                    null,
                    new \Illuminate\View\ComponentAttributeBag([
                        "wire:loading.remove.delay." . config("filament.livewire_loading_delay", "default") => $hasLoadingIndicator,
                        "wire:target" => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ]),
                    size: $iconSize,
                )
            }}
        @endif

        @if ($hasLoadingIndicator)
            {{
                \Filament\Support\generate_loading_indicator_html(
                    new \Illuminate\View\ComponentAttributeBag([
                        "wire:loading.delay." . config("filament.livewire_loading_delay", "default") => "",
                        "wire:target" => $loadingIndicatorTarget,
                    ]),
                    size: $iconSize,
                )
            }}
        @endif

        @if ($hasFormProcessingLoadingIndicator)
            {{
                \Filament\Support\generate_loading_indicator_html(
                    new \Illuminate\View\ComponentAttributeBag([
                        "x-cloak" => "x-cloak",
                        "x-show" => "isProcessing",
                    ]),
                    size: $iconSize,
                )
            }}
        @endif
    @endif

    @if (! ($labelSrOnly ?? false))
        @if ($hasFormProcessingLoadingIndicator)
            <span x-show="! isProcessing">
                {{ $label ?? "" }}
            </span>
        @else
            {{ $label ?? "" }}
        @endif
    @endif

    @if ($hasFormProcessingLoadingIndicator && ! ($labelSrOnly ?? false))
        <span x-cloak x-show="isProcessing" x-text="processingMessage"></span>
    @endif

    @if (($iconPosition ?? IconPosition::Before) === IconPosition::After)
        @if ($icon ?? null)
            {{
                \Filament\Support\generate_icon_html(
                    $icon,
                    null,
                    new \Illuminate\View\ComponentAttributeBag([
                        "wire:loading.remove.delay." . config("filament.livewire_loading_delay", "default") => $hasLoadingIndicator,
                        "wire:target" => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ]),
                    size: $iconSize,
                )
            }}
        @endif

        @if ($hasLoadingIndicator)
            {{
                \Filament\Support\generate_loading_indicator_html(
                    new \Illuminate\View\ComponentAttributeBag([
                        "wire:loading.delay." . config("filament.livewire_loading_delay", "default") => "",
                        "wire:target" => $loadingIndicatorTarget,
                    ]),
                    size: $iconSize,
                )
            }}
        @endif

        @if ($hasFormProcessingLoadingIndicator)
            {{
                \Filament\Support\generate_loading_indicator_html(
                    new \Illuminate\View\ComponentAttributeBag([
                        "x-cloak" => "x-cloak",
                        "x-show" => "isProcessing",
                    ]),
                    size: $iconSize,
                )
            }}
        @endif
    @endif

    @if (filled($badge ?? null))
        <div class="fi-btn-badge-ctn">
            @if ($badge instanceof \Illuminate\View\ComponentSlot)
                {{ $badge }}
            @else
                <span
                    {{
                        (new ComponentAttributeBag())->color(\Filament\Support\View\Components\BadgeComponent::class, $badgeColor ?? "primary")->class([
                            "fi-badge",
                            ($badgeSize ?? Size::ExtraSmall) instanceof Size ? "fi-size-{$badgeSize->value}" : (is_string($badgeSize) ? $badgeSize : ""),
                        ])
                    }}
                >
                    {{ $badge }}
                </span>
            @endif
        </div>
    @endif
</{{ $tag ?? "button" }}>
