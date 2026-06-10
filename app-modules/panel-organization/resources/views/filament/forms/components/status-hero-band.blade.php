@php
    $statePath = $getStatePath();
    $options = $getOptions();
    $livewireKey = $getLivewireKey();
    $isDisabled = $isDisabled();
    $wireModel = $applyStateBindingModifiers('wire:model');

    // Cor de acento por status (presentação local do campo). O segmento ativo
    // se expande e é preenchido com a cor do seu status.
    $accents = [
        'new' => '#6b7280',
        'in_review' => '#cb9f57',
        'in_progress' => '#6b8cba',
        'offer_extended' => '#9684bb',
        'offer_accepted' => '#5dab90',
        'offer_declined' => '#c47e7e',
        'hired' => '#6cb07f',
        'withdrawn' => '#cb8f5c',
        'rejected' => '#64748b',
    ];
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        .he4rt-status-band {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 6px;
            border: 1px solid var(--outline-low);
            border-radius: 16px;
            background: var(--elevation-02dp);
        }
        .he4rt-status-band input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }
        .he4rt-status-band label {
            display: flex;
            flex: 1 1 0;
            min-width: 116px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 15px 12px;
            border-radius: 11px;
            color: var(--text-medium);
            font-weight: 600;
            font-size: 13.5px;
            line-height: 1.15;
            text-align: center;
            cursor: pointer;
            transition:
                flex 0.25s ease,
                background 0.25s ease,
                color 0.2s ease,
                box-shadow 0.2s ease;
        }
        .he4rt-status-band label .dot {
            flex: 0 0 auto;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            transition: background 0.2s;
        }
        .he4rt-status-band label:hover {
            color: var(--text-high);
        }
        .he4rt-status-band input:checked + label {
            flex: 2.4 1 0;
            background: var(--accent);
            color: #0a0a0a;
            font-weight: 700;
            box-shadow: 0 10px 26px -12px var(--accent);
        }
        .he4rt-status-band input:checked + label .dot {
            background: rgba(0, 0, 0, 0.55);
        }
        .he4rt-status-band input:focus-visible + label {
            outline: 2px solid var(--text-high);
            outline-offset: 2px;
        }
    </style>

    <div class="he4rt-status-band" role="radiogroup">
        @foreach ($options as $value => $label)
            @php
                $id = $livewireKey . '-status-' . $loop->index;
            @endphp

            <input
                type="radio"
                id="{{ $id }}"
                value="{{ $value }}"
                {{ $wireModel }}="{{ $statePath }}"
                @disabled($isDisabled)
            />
            <label for="{{ $id }}" style="--accent: {{ $accents[$value] ?? '#6b7280' }}">
                <span class="dot"></span>
                {{ $label }}
            </label>
        @endforeach
    </div>
</x-dynamic-component>
