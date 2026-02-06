@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $candidate = $record->candidate;
    $summary = $candidate->summary;

    $hasContent = ! empty(trim($summary ?? ''));
@endphp

<x-filament::section icon="heroicon-o-user" icon-color="info">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.professional_summary.title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.professional_summary.subtitle') }}
    </x-slot>

    @if ($hasContent)
        <x-slot name="afterHeader">
            <x-he4rt::tag size="sm">
                {{ __('panel-organization::view.tabs.professional_summary.complete') }}
            </x-he4rt::tag>
        </x-slot>
    @endif

    @if ($hasContent)
        <x-filament::section :secondary="true">
            <div class="prose prose-sm max-w-none">
                <div class="text-text-high text-base leading-7">
                    {{ $summary }}
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section :secondary="true">
            <div class="text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::DocumentText"
                    size="lg"
                    class="text-text-low mx-auto"
                />
                <x-he4rt::heading class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.professional_summary.no_summary') }}
                </x-he4rt::heading>
                <p class="text-text-medium mt-2 text-sm">
                    {{ __('panel-organization::view.tabs.professional_summary.no_summary_text') }}
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
