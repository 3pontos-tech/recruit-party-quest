@php
    $fieldWrapperView = $getFieldWrapperView();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $canSelectPlaceholder = $canSelectPlaceholder();
    $isAutofocused = $isAutofocused();
    $isDisabled = $isDisabled();
    $isMultiple = $isMultiple();
    $isReorderable = $isReorderable();
    $isSearchable = $isSearchable();
    $hasInitialNoOptionsMessage = $hasInitialNoOptionsMessage();
    $canOptionLabelsWrap = $canOptionLabelsWrap();
    $isRequired = $isRequired();
    $isConcealed = $isConcealed();
    $isHtmlAllowed = $isHtmlAllowed();
    $isNative = ! ($isSearchable || $isMultiple) && $isNative();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $key = $getKey();
    $id = $getId();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixIconColor = $getPrefixIconColor();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixIconColor = $getSuffixIconColor();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
    $state = $getState();
    $livewireKey = $getLivewireKey();
    $icon = $getIcon();
    $iconColor = $getIconColor();
@endphp

<x-dynamic-component :component="$fieldWrapperView" class="fi-fo-select-wrp">
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
                :x-on:focus-input.stop="$isNative ? '$el.querySelector(\'select\')?.focus()' : '$el.querySelector(\'.fi-select-input-btn\')?.focus()'"
                :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                        ->class([
                            'fi-fo-select',
                            'fi-fo-select-has-inline-prefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'fi-fo-select-native' => $isNative,
                            'min-w-[200px]'
                        ])
                "
            >
                @if ($isNative)
                    <select
                        {{
                            $extraInputAttributeBag
                                ->merge(
                                    [
                                        'autofocus' => $isAutofocused,
                                        'disabled' => $isDisabled,
                                        'id' => $id,
                                        'required' => $isRequired && ! $isConcealed,
                                        $applyStateBindingModifiers('wire:model') => $statePath,
                                    ],
                                    escape: false,
                                )
                                ->class(['fi-select-input', 'fi-select-input-has-inline-prefix' => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel))])
                        }}
                    >
                        @if ($canSelectPlaceholder)
                            <option value="">
                                @if (! $isDisabled)
                                    {{ $getPlaceholder() }}
                                @endif
                            </option>
                        @endif

                        @foreach ($getOptions() as $value => $label)
                            @if (is_array($label))
                                <optgroup label="{{ $value }}">
                                    @foreach ($label as $groupedValue => $groupedLabel)
                                        <option
                                            @disabled($isOptionDisabled($groupedValue, $groupedLabel))
                                            value="{{ $groupedValue }}"
                                        >
                                            @if ($isHtmlAllowed)
                                                {!! $groupedLabel !!}
                                            @else
                                                {{ $groupedLabel }}
                                            @endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option @disabled($isOptionDisabled($value, $label)) value="{{ $value }}">
                                    @if ($isHtmlAllowed)
                                        {!! $label !!}
                                    @else
                                        {{ $label }}
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                @else
                    <div
                        class="fi-hidden"
                        x-data="{
                            isDisabled: @js($isDisabled),
                            init() {
                                const container = $el.nextElementSibling
                                container.dispatchEvent(
                                    new CustomEvent('set-select-property', {
                                        detail: { isDisabled: this.isDisabled },
                                    }),
                                )
                            },
                        }"
                    ></div>
                    <div
                        x-load
                        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('select', 'filament/forms') }}"
                        x-data="selectFormComponent({
                                    canOptionLabelsWrap: @js($canOptionLabelsWrap),
                                    canSelectPlaceholder: @js($canSelectPlaceholder),
                                    getOptionLabelUsing: async () => {
                                        return await Livewire.fireAction(
                                            $wire.__instance,
                                            'callSchemaComponentMethod',
                                            [@js($key), 'getOptionLabel'],
                                            { async: true },
                                        )
                                    },
                                    getOptionLabelsUsing: async () => {
                                        return await Livewire.fireAction(
                                            $wire.__instance,
                                            'callSchemaComponentMethod',
                                            [@js($key), 'getOptionLabelsForJs'],
                                            { async: true },
                                        )
                                    },
                                    getOptionsUsing: async () => {
                                        return await Livewire.fireAction(
                                            $wire.__instance,
                                            'callSchemaComponentMethod',
                                            [@js($key), 'getOptionsForJs'],
                                            { async: true },
                                        )
                                    },
                                    getSearchResultsUsing: async (search) => {
                                        return await Livewire.fireAction(
                                            $wire.__instance,
                                            'callSchemaComponentMethod',
                                            [@js($key), 'getSearchResultsForJs', { search }],
                                            { async: true },
                                        )
                                    },
                                    hasDynamicOptions: @js($hasDynamicOptions()),
                                    hasDynamicSearchResults: @js($hasDynamicSearchResults()),
                                    hasInitialNoOptionsMessage: @js($hasInitialNoOptionsMessage),
                                    initialOptionLabel: @js(blank($state) || $isMultiple ? null : $getOptionLabel()),
                                    initialOptionLabels: @js(filled($state) && $isMultiple ? $getOptionLabelsForJs() : []),
                                    initialState: @js($state),
                                    isAutofocused: @js($isAutofocused),
                                    isDisabled: @js($isDisabled),
                                    isHtmlAllowed: @js($isHtmlAllowed),
                                    isMultiple: @js($isMultiple),
                                    isReorderable: @js($isReorderable),
                                    isSearchable: @js($isSearchable),
                                    livewireId: @js($this->getId()),
                                    loadingMessage: @js($getLoadingMessage()),
                                    maxItems: @js($getMaxItems()),
                                    maxItemsMessage: @js($getMaxItemsMessage()),
                                    noOptionsMessage: @js($getNoOptionsMessage()),
                                    noSearchResultsMessage: @js($getNoSearchResultsMessage()),
                                    options: @js($getOptionsForJs()),
                                    optionsLimit: @js($getOptionsLimit()),
                                    placeholder: @js($getPlaceholder()),
                                    position: @js($getPosition()),
                                    searchDebounce: @js($getSearchDebounce()),
                                    searchingMessage: @js($getSearchingMessage()),
                                    searchPrompt: @js($getSearchPrompt()),
                                    searchableOptionFields: @js($getSearchableOptionFields()),
                                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                                    statePath: @js($statePath),
                                })"
                        wire:ignore
                        wire:key="{{ $livewireKey }}.{{
                            substr(
                                md5(
                                    serialize([
                                        $isDisabled,
                                    ]),
                                ),
                                0,
                                64,
                            )
                        }}"
                        x-on:keydown.esc="select.dropdown.isActive && $event.stopPropagation()"
                        x-on:set-select-property="$event.detail.isDisabled ? select.disable() : select.enable()"
                        {{
                            $attributes
                                ->merge($getExtraAlpineAttributes(), escape: false)
                                ->class(['fi-select-input'])
                        }}
                    >
                        <div x-ref="select"></div>
                    </div>
                @endif
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>
</x-dynamic-component>
