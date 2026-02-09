<div
    x-data="{
        state: $wire.entangle('jobGenerationState'),
        messages: {
            success: @js(__('panel-organization::view.job_generation.success_message')),
        },
    }"
    x-show="state !== 'idle'"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/70 backdrop-blur-sm"
>
    <div
        class="mx-4 w-full max-w-md rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/10 dark:bg-gray-800 dark:ring-white/10"
    >
        <!-- SUCCESS -->
        <div
            x-show="state === 'success'"
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
                        :icon="\Filament\Support\Icons\Heroicon::CheckCircle"
                        class="h-12 w-12 text-emerald-600 dark:text-emerald-400"
                    />
                </div>
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="messages.success"></h3>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('panel-organization::view.job_generation.redirecting') }}
                </p>
            </div>
        </div>
    </div>
</div>
