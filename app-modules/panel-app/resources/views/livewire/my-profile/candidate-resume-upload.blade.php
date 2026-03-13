@php
    use He4rt\App\Livewire\ResumeFileUploadProgress;
@endphp

<x-filament::section
    :aside="true"
    :heading="__('panel-app::pages/settings.resume_upload.heading')"
    :description="__('panel-app::pages/settings.resume_upload.description')"
    class="w-full"
>
    @if ($isOnCooldown)
        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 shrink-0" />
            <span>
                {{
                    __('panel-app::pages/settings.resume_upload.cooldown_message', [
                        'days' => $cooldownDaysRemaining,
                    ])
                }}
            </span>
        </div>
    @else
        <div x-data="{ showModal: false, showUpload: false }" class="w-full space-y-4">
            {{-- Step 1: Initial trigger --}}
            <div x-show="!showModal && !showUpload" class="m-0 flex w-full">
                <x-filament::button
                    icon="heroicon-o-arrow-up-tray"
                    x-on:click="showModal = true"
                    class="mr-auto ml-auto"
                >
                    {{ __('panel-app::pages/settings.resume_upload.upload_button') }}
                </x-filament::button>
            </div>

            {{-- Step 2: Warning modal --}}
            <div
                x-show="showModal"
                x-cloak
                class="border-warning-200 bg-warning-50 dark:border-warning-700 dark:bg-warning-950 space-y-4 rounded-xl border p-4"
            >
                <div class="flex items-start gap-3">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        class="text-warning-500 mt-0.5 h-5 w-5 shrink-0"
                    />
                    <div class="space-y-1">
                        <p class="text-warning-800 dark:text-warning-200 text-sm font-semibold">
                            {{ __('panel-app::pages/settings.resume_upload.modal_title') }}
                        </p>
                        <p class="text-warning-700 dark:text-warning-300 text-sm">
                            {{ __('panel-app::pages/settings.resume_upload.modal_body') }}
                        </p>
                        <ul class="text-warning-700 dark:text-warning-300 mt-2 list-inside list-disc space-y-1 text-sm">
                            <li>{{ __('panel-app::pages/settings.resume_upload.modal_adds_experiences') }}</li>
                            <li>{{ __('panel-app::pages/settings.resume_upload.modal_adds_education') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <x-filament::button color="gray" x-on:click="showModal = false">
                        {{ __('panel-app::pages/settings.resume_upload.modal_cancel') }}
                    </x-filament::button>

                    <x-filament::button color="warning" x-on:click="showModal = false; showUpload = true">
                        {{ __('panel-app::pages/settings.resume_upload.modal_confirm') }}
                    </x-filament::button>
                </div>
            </div>

            {{-- Step 3: Upload form + progress (shown after confirmation) --}}
            <div x-show="showUpload" x-cloak class="space-y-4">
                <form wire:submit.prevent class="space-y-4">
                    {{ $this->form }}
                </form>

                @livewire(ResumeFileUploadProgress::class)
            </div>
        </div>
    @endif
</x-filament::section>
