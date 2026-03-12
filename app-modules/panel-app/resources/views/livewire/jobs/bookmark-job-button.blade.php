@props([
    'size' => 'sm',
])

<x-he4rt::button
    variant="outline"
    :size="$size"
    rounded="md"
    block="icon-only"
    wire:click.stop.prevent="toggle"
    :icon="$isSaved ? 'heroicon-s-bookmark' : 'heroicon-o-bookmark'"
    :class="$isSaved ? 'text-primary' : ''"
    class="px-4 py-2"
    aria-label="{{ $isSaved ? __('panel-app::filament.components.bookmark_button.remove_saved') : __('panel-app::filament.components.bookmark_button.save_job') }}"
/>
