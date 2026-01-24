@props([
    'title',
    'items' => null,
    'type' => 'checkbox',
])

@php
@endphp

<div class="border-border border-b pb-4">
    <x-he4rt::heading size="2xs" class="py-2">{{ $title }}</x-he4rt::heading>
    <div class="space-y-2 pt-2">
        @foreach ($items as $item)
            @if ($type == 'checkbox')
                <x-he4rt::checkbox
                    {{ $attributes->merge() }}
                    :label="$item->getLabel()"
                    :name="$item->name"
                    :value="$item->value"
                />
            @else
                <x-he4rt::radio
                    {{ $attributes->merge() }}
                    :label="$item->getLabel()"
                    :name="$item->name"
                    :value="$item->value"
                />
            @endif
        @endforeach
    </div>
</div>
