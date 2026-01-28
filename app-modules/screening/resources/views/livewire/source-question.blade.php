@php
    use He4rt\Applications\Enums\CandidateSourceEnum;
@endphp

<div>
    @error('value')
        <p class="mt-1 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

    <x-filament::input.wrapper>
        <x-filament::input.select wire:model="value">
            <option value="">{{ __('screening::question_types.single_choice.select_placeholder') }}</option>
            @foreach (CandidateSourceEnum::cases() as $source)
                <option value="{{ $source }}">
                    {{ $source->getLabel() }}
                </option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
