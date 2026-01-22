@props([
    "as" => "button",
    "href" => null,
    "type" => "button",
    "variant" => "solid", // solid | outline
    "size" => "md", // xs | sm | md | lg
    "rounded" => "md", // sm | md | lg | full
    "block" => false,
    "disabled" => false,
    "loading" => false,
    "icon" => null,
    "iconTrailing" => null,
    "iconLeading" => null,
])

@php
    $isLink = filled($href);
    $tag = $isLink ? "a" : $as;
    $isBusy = $loading;
    $isDisabled = $disabled || $isBusy;

    $iconTrailing ??= $attributes->get("icon:trailing");
    $iconLeading = $icon ??= $iconLeading;

    $hasLabel = $slot->isNotEmpty();
    $isSquare = ! $hasLabel;

    $classes = collect([
        "hp-button",
        "hp-button-" . $variant,
        "hp-button-size-" . $size,
        "hp-button-rounded-" . $rounded,
        $block ? "hp-button-block" : null,
        $isSquare ? "hp-button-square" : null,
    ])
        ->filter()
        ->implode(" ");
@endphp

<{{ $tag }}
    @if (! $isLink)
        type="{{ $type }}"
        @if ($isDisabled)
            disabled
        @endif
    @else
        href="{{ $href }}"
        @if ($isDisabled)
            aria-disabled="true"
            tabindex="-1"
        @endif
    @endif
    @if ($isBusy) aria-busy="true" @endif
    {{ $attributes->class($classes) }}
>
    @if ($iconLeading)
        <x-he4rt::icon :icon="$iconLeading" />
    @elseif (isset($leading))
        {{ $leading }}
    @endif

    @if ($hasLabel)
        <span class="{{ $isBusy ? "opacity-0" : "opacity-100" }}">
            {{ $slot }}
        </span>
    @endif

    @if ($iconTrailing)
        <x-he4rt::icon :icon="$iconTrailing" />
    @elseif (isset($trailing))
        {{ $trailing }}
    @endif

    @if ($isBusy)
        <span class="hp-button-spinner">
            <svg class="animate-spin" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path
                    class="opacity-75"
                    d="M4 12a8 8 0 018-8"
                    stroke="currentColor"
                    stroke-width="4"
                    stroke-linecap="round"
                />
            </svg>
        </span>
    @endif
</{{ $tag }}>
