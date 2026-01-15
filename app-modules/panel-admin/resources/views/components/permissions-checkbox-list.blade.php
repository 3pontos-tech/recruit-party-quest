@php
    use Filament\Support\Enums\GridDirection;
    use Illuminate\Support\Str;

    $fieldWrapperView = $getFieldWrapperView();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $isHtmlAllowed = $isHtmlAllowed();
    $gridDirection = $getGridDirection() ?? GridDirection::Column;
    $isBulkToggleable = $isBulkToggleable();
    $isDisabled = $isDisabled();
    $isSearchable = $isSearchable();
    $statePath = $getStatePath();
    $options = $getOptions();
    $livewireKey = $getLivewireKey();
    $wireModelAttribute = $applyStateBindingModifiers("wire:model");

    $parsedOptions = collect($options)->map(function ($option, $key) {
        $parts = explode("-", $option);
        return [
            "key" => (string) $key,
            "resource_group" => $parts[0] ?? "Other",
            "resource_model" => $parts[1] ?? "Other",
            "action" => $parts[2] ?? "Other",
            "name" => $parts[3] ?? $option,
        ];
    });

    $groupedOptions = $parsedOptions->groupBy("resource_group");

    $desiredActionOrder = ["view_any", "view", "create", "update", "delete", "restore", "force_delete"];

    $presentActions = $parsedOptions
        ->pluck("action")
        ->unique()
        ->toArray();

    $allActions = collect($desiredActionOrder)
        ->filter(fn ($a) => in_array($a, $presentActions))
        ->merge(collect($presentActions)->diff($desiredActionOrder))
        ->values()
        ->toArray();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc("checkbox-list", "filament/forms") }}"
        x-data="checkboxListFormComponent({
                    livewireId: @js($this->getId()),
                })"
        {{ $getExtraAlpineAttributeBag()->class(["fi-fo-checkbox-list"]) }}
    >
        @if (! $isDisabled)
            @if ($isSearchable)
                <x-filament::input.wrapper
                    inline-prefix
                    :prefix-icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
                    :prefix-icon-alias="\Filament\Forms\View\FormsIconAlias::COMPONENTS_CHECKBOX_LIST_SEARCH_FIELD"
                    class="fi-fo-checkbox-list-search-input-wrp"
                >
                    <input
                        placeholder="{{ $getSearchPrompt() }}"
                        type="search"
                        x-model.debounce.{{ $getSearchDebounce() }}="search"
                        class="fi-input fi-input-has-inline-prefix"
                    />
                </x-filament::input.wrapper>
            @endif

            @if ($isBulkToggleable && count($options))
                <div x-cloak class="fi-fo-checkbox-list-actions" wire:key="{{ $livewireKey }}.actions">
                    <span
                        x-show="! areAllCheckboxesChecked"
                        x-on:click="toggleAllCheckboxes()"
                        wire:key="{{ $livewireKey }}.actions.select-all"
                    >
                        {{ $getAction("selectAll") }}
                    </span>

                    <span
                        x-show="areAllCheckboxesChecked"
                        x-on:click="toggleAllCheckboxes()"
                        wire:key="{{ $livewireKey }}.actions.deselect-all"
                    >
                        {{ $getAction("deselectAll") }}
                    </span>
                </div>
            @endif
        @endif

        <div
            {{
                $getExtraAttributeBag()
                    ->merge(
                        [
                            "x-show" => $isSearchable ? "visibleCheckboxListOptions.length" : null,
                        ],
                        escape: false,
                    )
                    ->class(["fi-fo-checkbox-list-options", "mt-5", "space-y-8"])
            }}
        >
            @forelse ($groupedOptions as $group => $groupItems)
                <div wire:key="{{ $livewireKey }}.groups.{{ $group }}" class="space-y-4">
                    <h3 class="border-b pb-2 text-xl font-bold dark:border-white/10">{{ $group }}</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($groupItems->groupBy("resource_model") as $resourceModel => $permissions)
                            <x-filament::section
                                :heading="$resourceModel"
                                wire:key="{{ $livewireKey }}.groups.{{ $group }}.models.{{ $resourceModel }}"
                                compact
                            >
                                <div class="grid grid-cols-1 gap-1">
                                    @foreach ($permissions->sortBy(fn ($p) => array_search($p["action"], $allActions)) as $permission)
                                        @php
                                            $label = $permission["name"];
                                            $value = $permission["key"];
                                        @endphp

                                        <div
                                            wire:key="{{ $livewireKey }}.options.{{ $value }}"
                                            @if ($isSearchable)
                                                x-show="
                                                    $el
                                                        .querySelector('.fi-fo-checkbox-list-option-label')
                                                        ?.innerText.toLowerCase()
                                                        .includes(search.toLowerCase()) ||
                                                        $el
                                                            .querySelector('.fi-fo-checkbox-list-option-description')
                                                            ?.innerText.toLowerCase()
                                                            .includes(search.toLowerCase())
                                                "
                                            @endif
                                            class="fi-fo-checkbox-list-option-ctn"
                                        >
                                            <label
                                                class="fi-fo-checkbox-list-option flex cursor-pointer items-center gap-2 py-1"
                                            >
                                                <input
                                                    type="checkbox"
                                                    {{
                                                        $extraInputAttributeBag
                                                            ->merge(
                                                                [
                                                                    "disabled" => $isDisabled || $isOptionDisabled($value, $label),
                                                                    "value" => $value,
                                                                    "wire:loading.attr" => "disabled",
                                                                    $wireModelAttribute => $statePath,
                                                                    "x-on:change" => $isBulkToggleable ? "checkIfAllCheckboxesAreChecked()" : null,
                                                                ],
                                                                escape: false,
                                                            )
                                                            ->class(["fi-checkbox-input", "fi-valid" => ! $errors->has($statePath), "fi-invalid" => $errors->has($statePath)])
                                                    }}
                                                />

                                                <div class="fi-fo-checkbox-list-option-text">
                                                    <span class="fi-fo-checkbox-list-option-label text-sm">
                                                        @if ($isHtmlAllowed)
                                                            {!! $label !!}
                                                        @else
                                                            {{ $label }}
                                                        @endif
                                                    </span>

                                                    @if ($hasDescription($value))
                                                        <p
                                                            class="fi-fo-checkbox-list-option-description text-xs text-gray-500 dark:text-gray-400"
                                                        >
                                                            {{ $getDescription($value) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </x-filament::section>
                        @endforeach
                    </div>
                </div>
            @empty
                <div wire:key="{{ $livewireKey }}.empty"></div>
            @endforelse
        </div>

        @if ($isSearchable)
            <div
                x-cloak
                x-show="search && ! visibleCheckboxListOptions.length"
                class="fi-fo-checkbox-list-no-search-results-message"
            >
                {{ $getNoSearchResultsMessage() }}
            </div>
        @endif
    </div>
</x-dynamic-component>
