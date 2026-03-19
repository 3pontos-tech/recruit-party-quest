@props([
    'repository',
    'canAnalyze' => true,
])
@php
    $escapedName = addslashes($repository['full_name']);
    $wireClick = $canAnalyze ? "submitAnalysis('{$escapedName}')" : null;
@endphp

<x-he4rt::card
    :interactive="$canAnalyze"
    :title="$canAnalyze ? __('repo-analysis::labels.components.repository_card.analyze_button') : null"
    wire:click="{{ $wireClick ?? '' }}"
    @class(['cursor-pointer' => $canAnalyze])
>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <x-he4rt::heading :level="3" size="sm" class="truncate font-mono text-gray-900 dark:text-white">
                    {{ $repository['name'] }}
                </x-he4rt::heading>
                @if ($repository['private'])
                    <x-he4rt::icon icon="heroicon-o-lock-closed" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                @else
                    <x-he4rt::icon icon="heroicon-o-globe-alt" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                @endif
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-panel-app::repo-analysis.language-badge :language="$repository['language']" />

                @if ($repository['default_branch'])
                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                        <x-he4rt::icon icon="heroicon-o-code-bracket" class="h-4 w-4" />
                        {{ $repository['default_branch'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</x-he4rt::card>
