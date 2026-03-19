@props([
    'repositories',
])

<x-filament::section class="my-4">
    <div class="mb-4">
        <x-he4rt::heading :level="2" size="xl" class="text-gray-900 dark:text-white">
            {{ __('repo-analysis::labels.components.repository_grid.heading') }}
        </x-he4rt::heading>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            @if (count($repositories) === 1)
                {{ __('repo-analysis::labels.components.repository_grid.count_singular', ['count' => count($repositories)]) }}
            @else
                {{ __('repo-analysis::labels.components.repository_grid.count_plural', ['count' => count($repositories)]) }}
            @endif
        </p>
    </div>

    @if (count($repositories) > 0)
        <div class="grid gap-3 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
            @foreach ($repositories as $repo)
                <x-panel-app::repo-analysis.repository-card :repository="$repo" />
            @endforeach
        </div>
    @else
        <div
            class="flex flex-col items-center justify-center gap-4 rounded-lg border border-dashed border-gray-300 py-12 dark:border-gray-700"
        >
            <x-he4rt::icon icon="heroicon-o-folder" class="h-12 w-12 text-gray-400" />
            <div class="text-center">
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ __('repo-analysis::labels.components.repository_grid.empty.heading') }}
                </p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('repo-analysis::labels.components.repository_grid.empty.description') }}
                </p>
            </div>
        </div>
    @endif
</x-filament::section>
