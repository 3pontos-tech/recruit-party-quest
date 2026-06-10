@php
    $statePath = $getStatePath();
    $options = $getOptions();
    $fromLabel = $getFromStageLabel();
    $isDisabled = $isDisabled();
    $wireModel = $applyStateBindingModifiers('wire:model');

    // Base de id segura para seletores CSS (getLivewireKey pode conter pontos).
    $base = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $getLivewireKey());
    $total = count($options) + 1; // marcos = âncora (etapa atual) + opções
    $edge = round(50 / $total, 4); // recuo da linha = centro do 1º/último marco
    $span = max($total - 1, 1); // nº de intervalos entre marcos

    // Regras :has() geradas por opção: a linha vai de centro a centro dos marcos,
    // então a barra preenche exatamente até o marco selecionado em m/(N-1). Sem JS.
    $rules = '';
    $i = 0;
    foreach ($options as $value => $name) {
        $width = round((($i + 1) / $span) * 100, 2);
        $sel = '#' . $base . '-trk-' . $i . ':checked';
        $rules .= '.he4rt-stage-track:has(' . $sel . ') .fill{width:' . $width . '%;}';
        $rules .= '.he4rt-stage-track:has(' . $sel . ') .miles>.mile:nth-child(-n+' . ($i + 2) . ') .md{background:var(--stage-accent);outline-color:var(--stage-accent);color:#fff;}';
        $i++;
    }
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <style>
        .he4rt-stage-track {
            --stage-accent: #6b8cba;
            position: relative;
            padding-top: 5px;
        }
        .he4rt-stage-track .line {
            position: absolute;
            top: 19px;
            right: var(--edge);
            left: var(--edge);
            height: 4px;
            border-radius: 4px;
            background: var(--outline-low);
        }
        .he4rt-stage-track .fill {
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--stage-accent), #9684bb);
            transition: width 0.35s ease;
        }
        .he4rt-stage-track .miles {
            position: relative;
            display: flex;
        }
        .he4rt-stage-track .mile {
            display: flex;
            flex: 1;
            flex-direction: column;
            align-items: center;
            gap: 9px;
            text-align: center;
        }
        .he4rt-stage-track label.mile {
            cursor: pointer;
        }
        .he4rt-stage-track input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }
        .he4rt-stage-track .md {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 3px solid var(--elevation-01dp);
            outline: 2px solid var(--outline-medium);
            background: var(--elevation-03dp);
            color: var(--text-low);
            font-size: 13px;
            transition:
                transform 0.16s ease,
                outline-color 0.2s,
                background 0.2s,
                color 0.2s;
        }
        .he4rt-stage-track .mc {
            max-width: 120px;
            color: var(--text-low);
            font-size: 12px;
            font-weight: 600;
        }
        .he4rt-stage-track .mile.anchor .md {
            background: var(--text-high);
            outline-color: var(--text-high);
            color: #0a0a0a;
        }
        .he4rt-stage-track .mile.anchor .mc {
            color: var(--text-medium);
        }
        .he4rt-stage-track label.mile:hover .md {
            transform: scale(1.08);
            outline-color: var(--outline-high);
        }
        .he4rt-stage-track .mile:has(input:checked) .md {
            transform: scale(1.18);
            background: var(--stage-accent);
            outline-color: var(--stage-accent);
            color: #fff;
        }
        .he4rt-stage-track .mile:has(input:checked) .mc {
            color: var(--text-high);
        }
        .he4rt-stage-track input:focus-visible + .md {
            outline-color: var(--text-high);
        }
        {!! $rules !!}
    </style>

    <div class="he4rt-stage-track" style="--edge: {{ $edge }}%">
        <div class="line"><div class="fill"></div></div>
        <div class="miles">
            <div class="mile anchor">
                <span class="md">✓</span>
                <span class="mc">
                    {{ $fromLabel ?? __('panel-organization::view.pipeline.stage_detail.current_stage') }}
                </span>
            </div>
            @foreach ($options as $value => $name)
                @php
                    $id = $base . '-trk-' . $loop->index;
                @endphp

                <label class="mile">
                    <input
                        type="radio"
                        id="{{ $id }}"
                        value="{{ $value }}"
                        {{ $wireModel }}="{{ $statePath }}"
                        @disabled($isDisabled)
                    />
                    <span class="md"></span>
                    <span class="mc">{{ $name }}</span>
                </label>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
