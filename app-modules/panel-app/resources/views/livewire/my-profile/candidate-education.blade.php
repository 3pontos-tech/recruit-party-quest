<x-filament::section
    :aside="true"
    :heading="__('panel-app::pages/settings.education.heading')"
    :description="__('panel-app::pages/settings.education.description')"
>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}
        <div class="text-right">
            <x-filament::button type="submit" class="align-right">
                {{ __('panel-app::pages/settings.education.submit') }}
            </x-filament::button>
        </div>
    </form>
</x-filament::section>
