@props([
    'show' => 'false',
    'maxWidth' => '2xl',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-2xl',
    };
@endphp

<div x-cloak x-show="{{ $show }}" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div
        x-show="{{ $show }}"
        x-transition:enter="duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="bg-elevation-surface/64 fixed inset-0 backdrop-blur-[2px] transition-opacity"
    ></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                x-show="{{ $show }}"
                x-transition:enter="duration-300 ease-out"
                x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave="duration-200 ease-in"
                x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave-end="translate-y-4 opacity-0 sm:translate-y-0 sm:scale-95"
                class="{{ $maxWidthClass }} bg-elevation-02dp relative w-full transform overflow-hidden rounded-md px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:p-6"
                @click.away="{{ $show }} = false"
            >
                @if (isset($title))
                    <div>
                        <x-he4rt::heading level="3" class="text-text-high">{{ $title }}</x-he4rt::heading>
                    </div>
                @endif

                <div class="mt-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
