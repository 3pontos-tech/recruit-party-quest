@props(['fails' => 0, 'total' => 0, 'answers' => 0])

@if ($answers === 0)
    <span
        {{ $attributes->class(['bg-outline-low/20 text-text-low inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_unanswered') }}
    </span>
@elseif ($fails > 0)
    <span
        {{ $attributes->class(['inline-flex items-center rounded-full bg-red-500/10 px-2 py-0.5 text-[11px] font-semibold text-red-600 ring-1 ring-red-500/30 ring-inset']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_failed', ['fails' => $fails, 'total' => $total]) }}
    </span>
@else
    <span
        {{ $attributes->class(['inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-semibold text-emerald-600 ring-1 ring-emerald-500/30 ring-inset']) }}
    >
        {{ __('panel-organization::workspace.queue.verdict_passed') }}
    </span>
@endif
