<x-he4rt::card
    variant="solid"
    density="normal"
    :interactive="false"
    class="bg-elevation-01dp/64 border-outline-light dark:border-outline-dark backdrop-blur-md"
>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <x-he4rt::heading level="3" size="md" class="text-text-high">
                {{ __('panel-app::filament.confidential.about_heading') }}
            </x-he4rt::heading>
            <x-he4rt::text size="sm" class="text-text-medium leading-relaxed">
                {{ __('panel-app::filament.confidential.about_description') }}
            </x-he4rt::text>
        </div>
    </div>
</x-he4rt::card>
