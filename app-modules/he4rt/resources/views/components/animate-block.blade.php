@props([
    "type" => "fade-up-glass",
    "delay" => null,
    "duration" => "700",
    "observe" => false,
])

@php
    $definitions = [
        "fade-up-glass" => [
            "start" => "translate-y-8 opacity-100",
            "end" => "translate-y-0 opacity-100",
        ],
        "fade-up" => [
            "start" => "translate-y-8 opacity-0",
            "end" => "translate-y-0 opacity-100",
        ],
        "fade-down" => [
            "start" => "-translate-y-8 opacity-0",
            "end" => "translate-y-0 opacity-100",
        ],
        "fade-right" => [
            "start" => "-translate-x-8 opacity-0",
            "end" => "translate-x-0 opacity-100",
        ],
        "fade-left" => [
            "start" => "translate-x-8 opacity-0",
            "end" => "translate-x-0 opacity-100",
        ],
        "scale" => [
            "start" => "scale-90 opacity-0",
            "end" => "scale-100 opacity-100",
        ],
        "blur" => [
            "start" => "opacity-0 blur-sm",
            "end" => "blur-0 opacity-100",
        ],
    ];

    $anim = $definitions[$type];
    $transitionClass = "transition-all ease-in duration-" . $duration;
    if ($delay) {
        $transitionClass .= " delay-" . $delay;
    }
@endphp

<div
    @if ($observe)
        x-data="{ visible: false }"
        x-intersect.once.threshold.20="visible = true"
    @endif
    {{ $attributes }}
>
    <div class="{{ $transitionClass }} h-full" :class="visible ? '{{ $anim["end"] }}' : '{{ $anim["start"] }}'">
        {{ $slot }}
    </div>
</div>
