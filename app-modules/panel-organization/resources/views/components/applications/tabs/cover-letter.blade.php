@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $coverLetter = $record->cover_letter;
    $hasContent = ! empty(trim($coverLetter ?? ''));
@endphp

@if ($hasContent)
    <x-filament::section :icon="\Filament\Support\Icons\Heroicon::DocumentText" icon-color="success">
        <x-slot name="heading">
            {{ __('panel-organization::view.tabs.cover_letter.title') }}
        </x-slot>
        <x-slot name="description">
            {{ __('panel-organization::view.tabs.cover_letter.subtitle') }}
        </x-slot>
        <x-filament::section :secondary="true">
            <div class="prose prose-sm max-w-none">
                <div class="text-text-high text-base leading-7">
                    {{ $coverLetter }}
                </div>
            </div>
        </x-filament::section>
    </x-filament::section>
@endif
