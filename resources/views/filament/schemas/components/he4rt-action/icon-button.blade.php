@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\Enums\Size;
    use Illuminate\View\ComponentAttributeBag;

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
        Size::ExtraSmall => IconSize::Small,
        Size::Large, Size::ExtraLarge => IconSize::Large,
        default => null,
    };

    $loadingIndicator = $loadingIndicator ?? true;
    $wireTarget = $loadingIndicator
        ? $attributes
            ->whereStartsWith(["wire:target", "wire:click"])
            ->filter(fn ($value): bool => filled($value))
            ->first()
        : null;

    $hasLoadingIndicator = filled($wireTarget) || (($type ?? "button") === "submit" && filled($form ?? null));

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
    {{
        $attributes
            ->merge(
                [
                    "aria-disabled" => $isDisabled ? "true" : null,
                    "aria-label" => $label ?? null,
                    "disabled" => $isDisabled && blank($tooltip ?? null),
                    "form" => $formId ?? null,
                    "type" => ($tag ?? "button") === "button" ? $type ?? "button" : null,
                    "wire:loading.attr" => ($tag ?? "button") === "button" ? "disabled" : null,
                    "wire:target" => $hasLoadingIndicator && ($loadingIndicatorTarget ?? null) ? $loadingIndicatorTarget : null,
                ],
                escape: false,
            )
            ->merge(
                [
                    "title" => $hasTooltip ? null : $label ?? null,
                ],
                escape: true,
            )
            ->when(
                $isDisabled && $hasTooltip,
                fn (ComponentAttributeBag $attributes) => $attributes->filter(fn (mixed $value, string $key): bool => ! str($key)->startsWith(["href", "x-on:", "wire:click"])),
            )
            ->class(["hp-button", "hp-button-" . $variant, "hp-button-size-" . $hpSize, "hp-button-rounded-" . $rounded, "hp-button-square", "fi-disabled" => $isDisabled])
    }}
>
    {{
        \Filament\Support\generate_icon_html(
            $icon ?? null,
            null,
            new \Illuminate\View\ComponentAttributeBag([
                "wire:loading.remove.delay." . config("filament.livewire_loading_delay", "default") => $hasLoadingIndicator,
                "wire:target" => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
            ]),
            size: $iconSize,
        )
    }}

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

    @if (filled($badge ?? null))
        <div class="fi-icon-btn-badge-ctn">
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
