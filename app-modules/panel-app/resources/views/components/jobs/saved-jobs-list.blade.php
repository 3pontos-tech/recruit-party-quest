@props([
    'jobs',
])

<div class="divide-outline-light dark:divide-outline-dark divide-y">
    @forelse ($jobs as $saved)
        @php($job = $saved->jobRequisition)
        <x-panel-app::jobs.saved-job-card :job="$job" wire:key="saved-job-{{ $job->id }}">
            <x-slot:action>
                <button
                    type="button"
                    wire:click="removeSavedJob('{{ $job->id }}')"
                    title="{{ __('panel-app::filament.components.saved_jobs_widget.remove') }}"
                    aria-label="{{ __('panel-app::filament.components.saved_jobs_widget.remove') }}"
                    class="text-text-low hover:bg-red-primary/10 hover:text-red-primary flex size-7 items-center justify-center rounded-lg transition duration-200"
                >
                    <x-he4rt::icon icon="heroicon-m-trash" class="size-4" />
                </button>
            </x-slot>
        </x-panel-app::jobs.saved-job-card>
    @empty
        <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
            <p class="text-text-medium text-sm font-medium">
                {{ __('panel-app::filament.components.saved_jobs_widget.empty_title') }}
            </p>
            <p class="text-text-low text-xs">
                {{ __('panel-app::filament.components.saved_jobs_widget.empty_description') }}
            </p>
        </div>
    @endforelse
</div>
