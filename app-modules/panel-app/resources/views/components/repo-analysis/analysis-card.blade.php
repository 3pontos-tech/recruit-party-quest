@php
    use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisResultPage;
@endphp

@props([
    'analysis',
])

@php
    $formattedDate = $analysis->analyzed_at
        ? $analysis->analyzed_at->format('d M Y')
        : null;
@endphp

<x-he4rt::card interactive :href="RepoAnalysisResultPage::getUrl(['uuid' => $analysis->getKey()])">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <x-he4rt::heading :level="3" size="sm" class="truncate font-mono text-gray-900 dark:text-white">
                    {{ $analysis->repo_name }}
                </x-he4rt::heading>
                @if ($analysis->repo_is_private)
                    <x-he4rt::icon icon="heroicon-o-lock-closed" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                @else
                    <x-he4rt::icon icon="heroicon-o-globe-alt" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                @endif
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-panel-app::repo-analysis.language-badge :language="$analysis->repo_language" />
                @php
                    $statusColor = match ($analysis->status->value) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'analyzing' => 'info',
                        default => 'gray',
                    };
                @endphp

                <x-filament::badge :color="$statusColor">
                    {{ __('repo-analysis::labels.status.' . $analysis->status->value) }}
                </x-filament::badge>
            </div>

            @if ($formattedDate)
                <div class="mt-3 flex items-center gap-1 text-gray-500 dark:text-gray-400">
                    <x-he4rt::icon icon="heroicon-o-calendar" class="h-5 w-5" />
                    {{ $formattedDate }}
                </div>
            @endif

            @if ($analysis->status->value === 'analyzing')
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="bg-info-600 h-full w-2/3 animate-pulse rounded-full"></div>
                    </div>
                    <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        <x-he4rt::icon icon="heroicon-o-clock" class="h-3 w-3" />
                        {{ __('repo-analysis::labels.components.analysis_card.processing') }}
                    </span>
                </div>
            @endif
        </div>
    </div>
</x-he4rt::card>
