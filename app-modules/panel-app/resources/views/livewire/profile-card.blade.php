<aside class="sticky top-24 self-start">
    <div
        class="bg-elevation-01dp/32 border-outline-light dark:border-outline-dark flex flex-col gap-6 rounded-md border p-8 backdrop-blur-md"
    >
        <div class="flex items-center justify-between gap-2">
            <div class="flex flex-row items-center gap-3">
                <x-he4rt::avatar
                    src="{{ auth()->user()->getFilamentAvatarUrl() }}"
                    alt="{{ auth()->user()->name }}"
                    class="size-11"
                />

                <div class="flex flex-col justify-center">
                    <x-he4rt::text class="text-text-high">
                        {{ auth()->user()->name }}
                    </x-he4rt::text>
                    <x-he4rt::text>Designer</x-he4rt::text>
                </div>
            </div>

            <div>
                <x-he4rt::button icon="heroicon-o-pencil" variant="outline" size="sm">Editar perfil</x-he4rt::button>
            </div>
        </div>

        <div>registration progress</div>

        <div>contact info</div>
    </div>
</aside>
