@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $coverLetter = $record->cover_letter;
    $hasContent = ! empty(trim($coverLetter ?? ''));
@endphp

@if ($hasContent)
    <div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
        <div class="space-y-4">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <div
                    class="bg-success-100 text-success-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                >
                    <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::DocumentText" size="sm" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">Cover Letter</h3>
                    <p class="text-text-medium text-sm">Personal message from candidate</p>
                </div>
            </div>

            {{-- Cover Letter Content --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-6">
                <div class="prose prose-sm max-w-none">
                    <div class="text-text-high leading-relaxed">
                        {{ $coverLetter }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
