@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\View\ComponentAttributeBag;

    $fieldWrapperView = $getFieldWrapperView();
    $statePath = $getStatePath();

    $attributes = (new ComponentAttributeBag())
        ->merge(
            [
                'aria-checked' => 'false',
                'autofocus' => $isAutofocused(),
                'disabled' => $isDisabled(),
                'id' => $getId(),
                'offColor' => $getOffColor() ?? 'gray',
                'offIcon' => $getOffIcon(),
                'onColor' => $getOnColor() ?? 'primary',
                'onIcon' => $getOnIcon(),
                'state' => '$wire.' . $applyStateBindingModifiers('$entangle(\'' . $statePath . '\')'),
                'wire:loading.attr' => 'disabled',
                'wire:target' => $statePath,
            ],
            escape: false,
        )
        ->merge($getExtraAttributes(), escape: false)
        ->merge($getExtraAlpineAttributes(), escape: false)
        ->class(['fi-fo-toggle']);
    $icon = $getIcon();
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <x-filament::section :compact="true">
        <div class="flex items-center justify-between gap-3">
            <div class="flex gap-2">
                @if ($icon)
                    <div class="flex items-center rounded-lg bg-blue-500/10 p-2">
                        <x-he4rt::icon :icon="$icon" class="h-4 w-4 text-blue-400" />
                    </div>
                @endif

                <div class="flex gap-2">
                    <div class="space-y-1">
                        <x-he4rt::text size="xs" class="font-family-secondary text-text-high">
                            {{ __($field->getLabel()) }}
                        </x-he4rt::text>
                        <x-he4rt::text size="xs">
                            {{ $getDescription() }}
                        </x-he4rt::text>
                    </div>
                </div>
            </div>
            <x-filament::toggle :attributes="\Filament\Support\prepare_inherited_attributes($attributes)" />
        </div>
    </x-filament::section>
</x-dynamic-component>
