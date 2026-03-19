<x-filament-panels::page>
    <x-filament::section class="flex h-[50vh] flex-col items-center justify-center">
        <div @if($this->hasInProgressAnalyses()) wire:poll.5s="refreshAnalyses" @endif>
            @if (! $this->hasGitHubConnection)
                <div class="flex h-full flex-col items-center justify-center gap-6">
                    <x-filament::icon
                        icon="heroicon-o-code-bracket"
                        class="h-16 w-16 text-gray-400 dark:text-gray-600"
                    />
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('repo-analysis::labels.page.list.no_github.heading') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('repo-analysis::labels.page.list.no_github.description') }}
                        </p>
                    </div>
                    <x-filament::button tag="a" href="{{ route('github.connect') }}" icon="heroicon-o-link">
                        {{ __('repo-analysis::labels.page.list.no_github.button') }}
                    </x-filament::button>
                </div>
            @elseif ($this->analyses->isEmpty())
                <div class="flex flex-col items-center justify-center py-12">
                    <x-filament::icon
                        icon="heroicon-o-code-bracket"
                        class="h-16 w-16 text-gray-400 dark:text-gray-600"
                    />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('repo-analysis::labels.page.list.empty_heading') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('repo-analysis::labels.page.list.empty_description') }}
                    </p>
                </div>
            @else
                <x-panel-app::repo-analysis.analysis-grid :analyses="$this->analyses" />
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
