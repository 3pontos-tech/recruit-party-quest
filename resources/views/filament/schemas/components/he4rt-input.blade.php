@php
    use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
    use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;

    $fieldWrapperView = $getFieldWrapperView();
    $datalistOptions = $getDatalistOptions();
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $extraAttributeBag = $getExtraAttributeBag();
    $id = $getId();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $isPasswordRevealable = $isPasswordRevealable();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $mask = $getMask();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixIconColor = $getPrefixIconColor();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixIconColor = $getSuffixIconColor();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
    $placeholder = $getPlaceholder();
    $icon = $getIcon();
    $iconColor = $getIconColor();

    if ($isPasswordRevealable) {
        $xData = '{ isPasswordRevealed: false }';
    } elseif (count($extraAlpineAttributes) || filled($mask)) {
        $xData = '{}';
    } else {
        $xData = null;
    }

    if ($isPasswordRevealable) {
        $type = null;
    } elseif (filled($mask)) {
        $type = 'text';
    } else {
        $type = $getType();
    }

    $inputAttributes = $getExtraInputAttributeBag()
        ->merge($extraAlpineAttributes, escape: false)
        ->merge(
            [
                'autocapitalize' => $getAutocapitalize(),
                'autocomplete' => $getAutocomplete(),
                'autofocus' => $isAutofocused(),
                'disabled' => $isDisabled,
                'id' => $id,
                'inlinePrefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                'inlineSuffix' => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                'inputmode' => $getInputMode(),
                'list' => $datalistOptions ? $id . '-list' : null,
                'max' => ! $isConcealed ? $getMaxValue() : null,
                'maxlength' => ! $isConcealed ? $getMaxLength() : null,
                'min' => ! $isConcealed ? $getMinValue() : null,
                'minlength' => ! $isConcealed ? $getMinLength() : null,
                'placeholder' => filled($placeholder) ? e($placeholder) : null,
                'readonly' => $isReadOnly(),
                'required' => $isRequired() && ! $isConcealed,
                'step' => $getStep(),
                'type' => $type,
                $applyStateBindingModifiers('wire:model') => $statePath,
                'x-bind:type' => $isPasswordRevealable ? 'isPasswordRevealed ? \'text\' : \'password\'' : null,
                'x-mask' . ($mask instanceof \Filament\Support\RawJs ? ':dynamic' : '') => filled($mask) ? $mask : null,
            ],
            escape: false,
        )
        ->class([
            'fi-revealable' => $isPasswordRevealable,
        ]);
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <x-filament::section :compact="true">
        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-2">
                @if ($icon)
                    <div class="bg-{{ $iconColor }}-500/10 flex items-center rounded-lg p-2">
                        <x-he4rt::icon :icon="$icon" class="h-4 w-4 text-{{$iconColor}}-500" />
                    </div>
                @endif

                <div class="flex gap-2">
                    <div class="space-y-1">
                        <x-he4rt::text size="xs" class="font-family-secondary text-text-high">
                            {{ __($field->getLabel()) }}
                            @if ($isRequired)
                                <span class="text-red-500">*</span>
                            @endif
                        </x-he4rt::text>
                        <x-he4rt::text size="xs">
                            {{ $getDescription() }}
                        </x-he4rt::text>
                    </div>
                </div>
            </div>
            <x-filament::input.wrapper
                :disabled="$isDisabled"
                :inline-prefix="$isPrefixInline"
                :inline-suffix="$isSuffixInline"
                :prefix="$prefixLabel"
                :prefix-actions="$prefixActions"
                :prefix-icon="$prefixIcon"
                :prefix-icon-color="$prefixIconColor"
                :suffix="$suffixLabel"
                :suffix-actions="$suffixActions"
                :suffix-icon="$suffixIcon"
                :suffix-icon-color="$suffixIconColor"
                :valid="! $errors->has($statePath)"
                :x-data="$xData"
                x-on:focus-input.stop="$el.querySelector('input')?.focus()"
                :attributes="
                    \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                        ->class(['fi-fo-text-input', 'min-w-[200px]'])
                "
            >
                <input
                    {{
                        $inputAttributes->class([
                            'fi-input',
                            'fi-input-has-inline-prefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'fi-input-has-inline-suffix' => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                        ])
                    }}
                />
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>

    @if ($datalistOptions)
        <datalist id="{{ $id }}-list">
            @foreach ($datalistOptions as $option)
                <option value="{{ $option }}"></option>
            @endforeach
        </datalist>
    @endif
</x-dynamic-component>
