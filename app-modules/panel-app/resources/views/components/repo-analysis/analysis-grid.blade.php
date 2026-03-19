@props([
    'analyses',
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if (count($analyses) > 0)
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($analyses as $analysis)
                <x-panel-app::repo-analysis.analysis-card :analysis="$analysis" />
            @endforeach
        </div>
    @else
        <div
            class="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed border-gray-300 py-12 dark:border-gray-700"
        >
            <x-he4rt::icon icon="heroicon-o-document-magnifying-glass" class="h-12 w-12 text-gray-400" />
            <div class="text-center">
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ __('repo-analysis::labels.components.analysis_grid.empty.heading') }}
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('repo-analysis::labels.components.analysis_grid.empty.description') }}
                </p>
            </div>
        </div>
    @endif
</div>
