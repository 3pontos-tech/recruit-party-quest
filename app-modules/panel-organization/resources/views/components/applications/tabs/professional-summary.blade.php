@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $candidate = $record->candidate;
    $summary = $candidate->summary;

    $hasContent = ! empty(trim($summary ?? ''));
@endphp

<x-filament::section>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-info-100 text-info-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg">
                    <x-heroicon-o-user class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">Professional Summary</h3>
                    <p class="text-text-medium text-sm">Candidate's professional background and experience</p>
                </div>
            </div>
            @if ($hasContent)
                <div class="flex items-center gap-2">
                    <x-filament::badge size="sm" color="info">Complete</x-filament::badge>
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
                <x-heroicon-o-document-text class="text-text-low mx-auto h-16 w-16" />
                <h4 class="text-text-high mt-4 text-lg font-medium">No Professional Summary</h4>
                <p class="text-text-medium mt-2 text-sm">This candidate hasn't provided a professional summary yet.</p>
            </div>
        @endif
    </div>
</x-filament::section>
