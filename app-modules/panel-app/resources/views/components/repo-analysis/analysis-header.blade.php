@props([
    'analysis',
])

@php
    $formattedDate = $analysis->analyzed_at
        ? $analysis->analyzed_at->translatedFormat('d F Y')
        : null;
    $result = $analysis->result;
@endphp

<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <x-he4rt::heading :level="1" size="xl" class="font-mono text-gray-900 dark:text-white">
                    {{ $analysis->repo_full_name }}
                </x-he4rt::heading>
                @if ($analysis->repo_is_private)
                    <x-he4rt::icon icon="heroicon-o-lock-closed" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                @else
                    <x-he4rt::icon icon="heroicon-o-globe-alt" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                @endif
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-panel-app::repo-analysis.language-badge :language="$analysis->repo_language" />

                @if ($result['detected_stack']['architecture'] ?? null)
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300"
                    >
                        <x-he4rt::icon icon="heroicon-o-puzzle-piece" class="h-3 w-3" />
                        {{ $result['detected_stack']['architecture'] }}
                    </span>
                @endif

                @if ($result['detected_stack']['framework'] ?? null)
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                    >
                        <x-he4rt::icon icon="heroicon-o-cube" class="h-3 w-3" />
                        {{ $result['detected_stack']['framework'] }}
                    </span>
                @endif

                @if ($formattedDate)
                    <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        <x-he4rt::icon icon="heroicon-o-calendar" class="h-4 w-4" />
                        {{ $formattedDate }}
                    </span>
                @endif
            </div>
        </div>

        <x-filament::button
            tag="a"
            :href="$analysis->repo_url"
            target="_blank"
            rel="noopener noreferrer"
            icon="heroicon-o-arrow-top-right-on-square"
            outlined
            size="sm"
        >
            {{ __('repo-analysis::labels.components.analysis_header.view_on_github') }}
        </x-filament::button>
    </div>

    {{-- Dependências Principais --}}
    @if (! empty($result['detected_stack']['main_dependencies']) && count($result['detected_stack']['main_dependencies']) > 0)
        <x-he4rt::card class="h-auto">
            <x-slot:title>
                {{ __('repo-analysis::labels.components.detected_stack.dependencies_heading') }}
            </x-slot>
            <div class="flex flex-wrap gap-2 pt-2">
                @foreach ($result['detected_stack']['main_dependencies'] as $dep)
                    <x-he4rt::tag>
                        {{ $dep }}
                    </x-he4rt::tag>
                @endforeach
            </div>
        </x-he4rt::card>
    @endif
</div>
