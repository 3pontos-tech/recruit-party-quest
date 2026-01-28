@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $candidate = $record->candidate;
    $summary = $candidate->summary;

    $hasContent = ! empty(trim($summary ?? ''));
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-info-100 text-info-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg">
                    <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::User" size="sm" />
                </div>
                <div>
                    <x-he4rt::heading class="text-text-high text-lg font-semibold">
                        {{ __('panel-organization::view.tabs.professional_summary.title') }}
                    </x-he4rt::heading>
                    <p class="text-text-medium text-sm">
                        {{ __('panel-organization::view.tabs.professional_summary.subtitle') }}
                    </p>
                </div>
            </div>
            @if ($hasContent)
                <div class="flex items-center gap-2">
                    <x-he4rt::tag size="sm">
                        {{ __('panel-organization::view.tabs.professional_summary.complete') }}
                    </x-he4rt::tag>
                </div>
            @endif
        </div>

        @if ($hasContent)
            {{-- Summary Content --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-6">
                <div class="prose prose-sm max-w-none">
                    <div class="text-text-high leading-relaxed">
                        {{ $summary }}
                    </div>
                </div>
            </div>
        @else
            {{-- No Summary State --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
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
        @endif
    </div>
</div>
