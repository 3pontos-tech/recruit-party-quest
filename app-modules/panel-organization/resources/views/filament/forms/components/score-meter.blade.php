@php
    $statePath = $getStatePath();
    $scores = array_keys($getOptions());
    $livewireKey = $getLivewireKey();
    $isDisabled = $isDisabled();
    $wireModel = $applyStateBindingModifiers('wire:model');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    {{--
        Medidor cumulativo: radios nativos + wire:model (mesmo binding do
        ToggleButtons pai). O preenchimento ●●●○○ usa o truque "star-rating":
        DOM em ordem reversa + flex-row-reverse o exibe 1→5, e
        `peer-checked ~ label` acende o ponto marcado e todos os anteriores.
    --}}
    <div class="he4rt-score-meter flex flex-row-reverse items-center justify-end gap-1.5 py-1">
        @foreach (array_reverse($scores) as $score)
            @php
                $id = $livewireKey . '-score-' . $score;
            @endphp

            <input
                type="radio"
                id="{{ $id }}"
                value="{{ $score }}"
                {{ $wireModel }}="{{ $statePath }}"
                @disabled($isDisabled)
                class="peer sr-only"
            />
            <label
                for="{{ $id }}"
                title="{{ $score }}"
                class="border-outline-medium hover:border-outline-high peer-checked:border-outline-high peer-checked:bg-outline-high h-6 w-6 cursor-pointer rounded-full border-2 bg-transparent transition"
            >
                <span class="sr-only">{{ $score }}</span>
            </label>
        @endforeach
    </div>
</x-dynamic-component>
