@php
    use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
@endphp

@script
    <script>
        const userId = '{{ auth()->id() }}';
        const channelName = `job-requisition.generation.${userId}`;

        window.Echo.private(channelName)
            .listen('.queued', (event) => {
                $wire.onQueued();
            })
            .listen('.processing', (event) => {
                $wire.onProcessing();
            })
            .listen('.success', (event) => {
                $wire.onSuccess(event);
            })
            .listen('.error', (event) => {
                $wire.onError(event);
            });
    </script>
@endscript

<div
    x-show="$wire.state !== 'idle'"
    x-cloak
    x-transition:enter="transition duration-300 ease-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-300 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-100 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
    style="display: none"
>
    <div
        class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/10 dark:bg-gray-800 dark:ring-white/10"
    >
        {{-- QUEUED STATE --}}
        <div
            x-show="$wire.state === '{{ JobGenerationStatus::Queued->value }}'"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="flex flex-col items-center gap-6 px-8 py-10 text-center"
        >
            <div class="relative">
                <div class="absolute inset-0 animate-pulse rounded-full bg-blue-500/20 blur-lg"></div>

                <div
                    class="relative flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900"
                >
                    <x-filament::icon
                        icon="heroicon-s-clock"
                        class="h-12 w-12 animate-pulse text-blue-600 dark:text-blue-400"
                    />
                </div>
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('recruitment::enums.job_generation_status.queued') }}
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('panel-organization::view.job_generation.queued_message') }}
                </p>
            </div>
        </div>

        {{-- PROCESSING STATE --}}
        <div
            x-show="$wire.state === '{{ JobGenerationStatus::Processing->value }}'"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="flex flex-col items-center gap-6 px-8 py-10 text-center"
        >
            <div class="relative">
                <div class="absolute inset-0 animate-spin rounded-full bg-purple-500/20 blur-lg"></div>

                <div
                    class="relative flex h-20 w-20 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900"
                >
                    <x-filament::icon
                        icon="heroicon-s-sparkles"
                        class="h-12 w-12 animate-pulse text-purple-600 dark:text-purple-400"
                    />
                </div>
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('recruitment::enums.job_generation_status.processing') }}
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('panel-organization::view.job_generation.processing_message') }}
                </p>
            </div>

            <div class="h-1.5 w-48 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-full w-full animate-pulse bg-linear-to-r from-purple-500 to-blue-500"></div>
            </div>
        </div>

        {{-- SUCCESS STATE --}}
        <div
            x-show="$wire.state === '{{ JobGenerationStatus::Success->value }}'"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="flex flex-col items-center gap-6 px-8 py-10 text-center"
        >
            <div class="relative">
                <div class="absolute inset-0 animate-pulse rounded-full bg-emerald-500/20 blur-lg"></div>

                <div
                    class="relative flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900"
                >
                    <x-filament::icon
                        icon="heroicon-s-check-circle"
                        class="h-12 w-12 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('recruitment::enums.job_generation_status.success') }}
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('panel-organization::view.job_generation.redirecting') }}
                </p>
            </div>
        </div>

        {{-- ERROR STATE --}}
        <div
            x-show="$wire.state === '{{ JobGenerationStatus::Error->value }}'"
            x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="flex flex-col items-center gap-6 px-8 py-10 text-center"
        >
            <div class="relative">
                <div class="absolute inset-0 animate-pulse rounded-full bg-red-500/20 blur-lg"></div>

                <div
                    class="relative flex h-20 w-20 items-center justify-center rounded-full bg-red-100 dark:bg-red-900"
                >
                    <x-filament::icon icon="heroicon-s-x-circle" class="h-12 w-12 text-red-600 dark:text-red-400" />
                </div>
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('recruitment::enums.job_generation_status.error') }}
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('panel-organization::view.job_generation.error_message') }}
                </p>
            </div>

            <x-filament::button color="gray" wire:click="closeOverlay">
                {{ __('panel-organization::view.job_generation.close_button') }}
            </x-filament::button>
        </div>
    </div>
</div>
