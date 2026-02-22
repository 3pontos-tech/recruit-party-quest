@php
    use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
@endphp

@script
    <script>
        const userId = '{{ auth()->id() }}';
        const channelName = `job-requisition.generation.${userId}`;

        window.Echo.private(channelName)
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
        {{-- PROCESSING STATE --}}
        <div
            x-data="{
                progress: 0,
                message: '',
                interval: null,

                init() {
                    this.$watch('$wire.state', (value) => {
                        if (value === '{{ JobGenerationStatus::Processing->value }}') {
                            this.startProgress()
                        } else if (value === '{{ JobGenerationStatus::Success->value }}') {
                            this.completeProgress()
                        }
                    })
                },

                startProgress() {
                    this.progress = 0
                    this.message =
                        '{{ __('panel-organization::view.job_generation.phases.analyzing') }}'

                    const duration = 12000
                    const targetProgress = 90
                    const updateInterval = 100
                    const increment = (targetProgress / duration) * updateInterval

                    if (this.interval) {
                        clearInterval(this.interval)
                    }

                    this.interval = setInterval(() => {
                        if (this.progress < targetProgress) {
                            this.progress = Math.min(
                                this.progress + increment,
                                targetProgress,
                            )
                            this.updateMessage()
                        } else {
                            clearInterval(this.interval)
                        }
                    }, updateInterval)
                },

                completeProgress() {
                    if (this.interval) {
                        clearInterval(this.interval)
                    }

                    this.message =
                        '{{ __('panel-organization::view.job_generation.phases.completed') }}'

                    const completeInterval = setInterval(() => {
                        if (this.progress < 100) {
                            this.progress = Math.min(this.progress + 5, 100)
                        } else {
                            clearInterval(completeInterval)
                        }
                    }, 50)
                },

                updateMessage() {
                    if (this.progress < 25) {
                        this.message =
                            '{{ __('panel-organization::view.job_generation.phases.analyzing') }}'
                    } else if (this.progress < 50) {
                        this.message =
                            '{{ __('panel-organization::view.job_generation.phases.generating') }}'
                    } else if (this.progress < 75) {
                        this.message =
                            '{{ __('panel-organization::view.job_generation.phases.customizing') }}'
                    } else {
                        this.message =
                            '{{ __('panel-organization::view.job_generation.phases.finalizing') }}'
                    }
                },
            }"
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

            {{-- Progress Section --}}
            <div class="w-full max-w-xs space-y-3">
                {{-- Phase Message --}}
                <p
                    x-text="message"
                    x-show="message"
                    x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="-translate-y-1 opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    class="text-sm font-medium text-purple-600 dark:text-purple-400"
                ></p>

                {{-- Progress Bar Container --}}
                <div class="relative">
                    {{-- Background Track --}}
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        {{-- Progress Bar --}}
                        <div
                            class="h-full bg-linear-to-r from-purple-500 to-blue-500 shadow-lg shadow-purple-500/30 transition-all duration-100 ease-linear"
                            :style="`width: ${progress}%`"
                        ></div>
                    </div>

                    {{-- Percentage Text --}}
                    <p
                        x-text="`${Math.round(progress)}%`"
                        class="mt-2 text-center text-xs font-semibold text-gray-600 tabular-nums dark:text-gray-400"
                    ></p>
                </div>
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
