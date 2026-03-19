<filament::page>
    @if (! $this->hasGitHubConnection)
        <div class="flex flex-col items-center justify-center gap-6 py-12">
            <x-filament::icon icon="heroicon-o-code-bracket" class="h-16 w-16 text-gray-400" />
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('repo-analysis::labels.page.new.no_github.heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('repo-analysis::labels.page.new.no_github.description') }}
                </p>
            </div>
            <x-filament::button tag="a" href="{{ route('github.connect') }}" icon="heroicon-o-link">
                {{ __('repo-analysis::labels.page.new.no_github.button') }}
            </x-filament::button>
        </div>
    @else
        <x-panel-app::repo-analysis.repository-grid :repositories="$this->repos" />
    @endif
</filament::page>
