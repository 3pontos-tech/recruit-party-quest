@php
    use He4rt\App\Filament\Pages\RepoAnalysis\RepoAnalysisListPage;
@endphp

<x-filament-panels::page>
    @if ($this->isAnalyzing())
        <div class="flex flex-col items-center justify-center gap-4 py-16" wire:poll.3s="refreshStatus">
            <x-filament::loading-indicator class="text-primary-500 h-10 w-10" />
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('repo-analysis::labels.page.result.analyzing') }}
            </p>
        </div>
    @elseif ($this->analysis->status->value === 'failed')
        <div class="border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-950 rounded-lg border p-6">
            <div class="text-danger-600 dark:text-danger-400 flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6" />
                <p class="font-medium">{{ __('repo-analysis::labels.page.result.failed') }}</p>
            </div>
        </div>
    @elseif ($this->analysis->result)
        @php
            $result = $this->analysis->result;
        @endphp

        {{-- Analysis Header --}}
        <x-panel-app::repo-analysis.analysis-header :analysis="$this->analysis" />

        {{-- Summary Section --}}
        @if (! empty($result['summary']))
            <div class="mt-6">
                <x-panel-app::repo-analysis.summary-section :summary="$result['summary']" />
            </div>
        @endif

        {{-- Highlights Section --}}
        @if (! empty($result['highlights']))
            <div class="mt-6">
                <x-panel-app::repo-analysis.highlights-section :highlights="$result['highlights']" />
            </div>
        @endif

        {{-- Category Sections --}}
        @foreach ($result['categories'] ?? [] as $category)
            <div class="mt-6">
                <x-panel-app::repo-analysis.category-section :category="$category" />
            </div>
        @endforeach

        {{-- Back link --}}
        <div class="mt-8">
            <a
                href="{{ RepoAnalysisListPage::getUrl() }}"
                wire:navigate
                class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 inline-flex items-center gap-2 text-sm font-medium transition"
            >
                <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
                {{ __('repo-analysis::labels.actions.back_to_list') }}
            </a>
        </div>
    @endif
</x-filament-panels::page>
