@if ($variant === 'icon-only')
    {{-- Icon Only Variant --}}
    <button
        type="button"
        wire:click.stop.prevent="toggle"
        class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 flex size-8 items-center justify-center rounded-lg border p-2 transition duration-200"
        aria-label="{{ $isSaved ? __('panel-app::filament.components.bookmark_button.remove_saved') : __('panel-app::filament.components.bookmark_button.save_job') }}"
    >
        @if ($isSaved)
            <x-he4rt::icon icon="heroicon-s-bookmark" class="text-primary size-4" />
        @else
            <x-he4rt::icon icon="heroicon-o-bookmark" class="size-4" />
        @endif
    </button>
@else
    {{-- Icon + Text Variant --}}
    <button
        type="button"
        wire:click.stop.prevent="toggle"
        class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 flex w-full flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition duration-200"
        aria-label="{{ $isSaved ? __('panel-app::filament.components.bookmark_button.remove_saved') : __('panel-app::filament.components.bookmark_button.save_job') }}"
    >
        @if ($isSaved)
            <x-he4rt::icon icon="heroicon-s-bookmark" class="size-4 text-yellow-300" />
            <span>{{ __('panel-app::filament.components.bookmark_button.saved') }}</span>
        @else
            <x-he4rt::icon icon="heroicon-o-bookmark" class="size-4" />
            <span>{{ __('panel-app::filament.components.bookmark_button.save') }}</span>
        @endif
    </button>
@endif
