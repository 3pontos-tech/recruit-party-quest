@props([
    'highlights',
])

@php
    $hasStrengths = ! empty($highlights['strengths']);
    $hasRisks = ! empty($highlights['main_risks']);
@endphp

@if ($hasStrengths || $hasRisks)
    <x-he4rt::card>
        <x-slot:title>
            {{ __('repo-analysis::labels.components.highlights_section.heading') }}
        </x-slot>

        <div class="grid gap-4 pt-4 md:grid-cols-2">
            {{-- Pontos Fortes --}}
            @if ($hasStrengths)
                <div
                    class="border-success-300 bg-success-50 dark:border-success-700/50 dark:bg-success-900/20 rounded-lg border p-4"
                >
                    <x-he4rt::heading
                        :level="4"
                        size="sm"
                        class="text-success-700 dark:text-success-400 mb-3 flex items-center gap-2"
                    >
                        <x-he4rt::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                        {{ __('repo-analysis::labels.components.highlights_section.strengths_heading') }}
                    </x-he4rt::heading>
                    <ul class="space-y-2">
                        @foreach ($highlights['strengths'] as $item)
                            <li class="text-success-700 dark:text-success-300/80 flex items-start gap-2 text-sm">
                                <span class="bg-success-600 mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Riscos / Débitos Técnicos --}}
            @if ($hasRisks)
                <div
                    class="border-danger-300 bg-danger-50 dark:border-danger-700/50 dark:bg-danger-900/20 rounded-lg border p-4"
                >
                    <x-he4rt::heading
                        :level="4"
                        size="sm"
                        class="text-danger-700 dark:text-danger-400 mb-3 flex items-center gap-2"
                    >
                        <x-he4rt::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 shrink-0" />
                        <span class="leading-none">
                            {{ __('repo-analysis::labels.components.highlights_section.risks_heading') }}
                        </span>
                    </x-he4rt::heading>
                    <ul class="space-y-2">
                        @foreach ($highlights['main_risks'] as $item)
                            <li class="text-danger-700 dark:text-danger-300/80 flex items-start gap-2 text-sm">
                                <span class="bg-danger-600 mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"></span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </x-he4rt::card>
@endif
