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
        <div x-data="{ showUpload: false }" class="w-full space-y-4">
            <div x-show="!showUpload" class="m-0 flex w-full">
                <x-filament::button
                    icon="heroicon-o-arrow-up-tray"
                    x-on:click="showUpload = true"
                    class="mr-auto ml-auto"
                >
                    {{ __('panel-app::pages/settings.resume_upload.upload_button') }}
                </x-filament::button>
            </div>

            <div x-show="showUpload" x-cloak class="space-y-4">
                <form wire:submit.prevent class="space-y-4">
                    {{ $this->form }}
                </form>
            </div>

            @livewire(ResumeFileUploadProgress::class)
        </div>
    @endif
</x-filament::section>
