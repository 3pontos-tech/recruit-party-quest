@props([
    'category',
])

@php
    $impactColors = [
        'high' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
        'medium' => 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
        'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp

<x-he4rt::card>
    <x-slot:title>
        {{ $category['name'] }}
    </x-slot>

    <div class="mb-4">
        <p class="text-gray-700 dark:text-gray-300">
            {{ $category['context'] ?? '' }}
        </p>
    </div>

    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        {{-- Problemas --}}
        @if (! empty($category['problems']))
            <div class="h-auto py-4">
                <x-he4rt::heading
                    :level="4"
                    size="sm"
                    class="text-danger-700 dark:text-danger-400 mb-3 flex items-center gap-2"
                >
                    <x-he4rt::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4" />
                    {{ __('repo-analysis::labels.components.category_section.problems_count', ['count' => count($category['problems'])]) }}
                </x-he4rt::heading>
                <ul class="space-y-4">
                    @foreach ($category['problems'] as $problem)
                        <li
                            class="rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50"
                        >
                            <div class="mb-2 flex items-start justify-between gap-4">
                                <span class="text-gray-900 dark:text-white">
                                    {{ $problem['title'] }}
                                </span>
                                <span
                                    class="{{ $impactColors[$problem['impact']] ?? $impactColors['low'] }} shrink-0 rounded-full px-2 py-0.5 text-xs"
                                >
                                    {{ __('repo-analysis::labels.impact_levels.' . ($problem['impact'] ?? 'low')) }}
                                </span>
                            </div>
                            <p class="mb-3 text-gray-700 dark:text-gray-300">
                                {{ $problem['description'] }}
                            </p>
                            <div class="border-primary-500 bg-primary-50 dark:bg-primary-900/10 border-l-2 p-3 text-sm">
                                <span class="text-primary-900 dark:text-primary-300 font-medium">
                                    {{ __('repo-analysis::labels.components.category_section.why_it_matters') }}:
                                </span>
                                <span class="text-primary-800 dark:text-primary-200/80">
                                    {{ $problem['why_it_matters'] }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Sugestões --}}
        @if (! empty($category['suggestions']))
            <div class="h-auto py-4">
                <x-he4rt::heading
                    :level="4"
                    size="sm"
                    class="text-success-700 dark:text-success-400 mb-3 flex items-center gap-2"
                >
                    <x-he4rt::icon icon="heroicon-o-light-bulb" class="h-4 w-4" />
                    {{ __('repo-analysis::labels.components.category_section.suggestions_count', ['count' => count($category['suggestions'])]) }}
                </x-he4rt::heading>
                <ul class="ml-5 space-y-2">
                    @foreach ($category['suggestions'] as $suggestion)
                        <li class="flex items-start gap-2 text-gray-700 dark:text-gray-300">
                            <span class="bg-success-600 mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"></span>
                            {{ $suggestion }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tópicos de Aprendizado --}}
        @if (! empty($category['study_topics']))
            <div class="py-4">
                <x-he4rt::heading
                    :level="4"
                    size="sm"
                    class="text-info-700 dark:text-info-400 mb-3 flex items-center gap-2"
                >
                    <x-he4rt::icon icon="heroicon-o-book-open" class="h-4 w-4" />
                    {{ __('repo-analysis::labels.components.category_section.study_topics_heading') }}
                </x-he4rt::heading>
                <div class="flex flex-wrap gap-2">
                    <ul class="ml-5 space-y-2">
                        @foreach ($category['study_topics'] as $topic)
                            <li class="flex items-start gap-2 text-gray-700 dark:text-gray-300">
                                <span class="bg-info-600 mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"></span>
                                {{ $topic }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</x-he4rt::card>
