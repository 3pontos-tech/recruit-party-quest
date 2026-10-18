@props(['score' => null, 'count' => null])

<span
    {{ $attributes->class(['inline-flex items-center gap-2']) }}
    title="{{ $score === null ? __('panel-organization::workspace.queue.no_evaluations') : __('panel-organization::workspace.queue.average', ['score' => $score]) }}"
>
    <span class="flex gap-0.5">
        @for ($i = 1; $i <= 5; $i++)
            <span
                class="{{ $score !== null && $i <= round($score) ? 'bg-emerald-500' : 'bg-outline-low/40' }} h-1.5 w-2.5 rounded-sm"
            ></span>
        @endfor
    </span>
    @if ($count !== null)
        <span class="text-text-low font-mono text-xs tabular-nums">{{ $count }}</span>
    @endif
</span>
