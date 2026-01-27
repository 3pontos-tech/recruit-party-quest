@php
    use He4rt\Applications\Enums\CandidateSourceEnum;
@endphp

<select
    wire:model.live="value"
    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
>
    @error('value')
        <p class="mt-1 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

    <option value="">{{ __('screening::question_types.single_choice.select_placeholder') }}</option>
    @foreach (CandidateSourceEnum::cases() as $source)
        <option value="{{ $source }}">
            {{ $source->getLabel() }}
        </option>
    @endforeach
</select>
