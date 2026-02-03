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

            <div class="self-start">
                <x-he4rt::button icon="heroicon-o-pencil" variant="outline" size="sm">
                    {{ __('panel-app::livewire/profile-card.edit_profile') }}
                </x-he4rt::button>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-2">
                <x-he4rt::text class="text-text-high">
                    {{ __('panel-app::livewire/profile-card.progress.title') }}
                </x-he4rt::text>
                <x-he4rt::text>50%</x-he4rt::text>
            </div>

            <div class="bg-border-outline-light dark:bg-border-outline-dark relative h-1 w-full rounded-full">
                <div class="bg-outline-high inset-0 h-1 rounded-full" style="width: var(--progress)"></div>
            </div>

            <div>
                <x-he4rt::text size="sm">
                    {{ __('panel-app::livewire/profile-card.progress.description') }}
                </x-he4rt::text>
            </div>
        </div>

        <div class="flex flex-col gap-8">
            <div class="flex flex-col gap-4">
                <x-he4rt::text class="text-text-high">
                    {{ __('panel-app::livewire/profile-card.contact.title') }}
                </x-he4rt::text>
                @forelse ($contactLinks as $link)
                    <x-he4rt::tag :icon="$link->icon" variant="ghost" :href="$link->url">
                        {{ $link->name }}
                    </x-he4rt::tag>
                @empty
                    <x-he4rt::text size="sm">
                        {{ __('panel-app::livewire/profile-card.contact.empty') }}
                    </x-he4rt::text>
                @endforelse
                <hr class="border-outline-light dark:border-outline-dark" />
            </div>

            <div class="flex flex-col gap-4">
                <x-he4rt::text class="text-text-high">
                    {{ __('panel-app::livewire/profile-card.social.title') }}
                </x-he4rt::text>
                @forelse ($socialLinks as $link)
                    <x-he4rt::tag :icon="$link->icon" variant="ghost" :href="$link->url">
                        {{ $link->name }}
                    </x-he4rt::tag>
                @empty
                    <x-he4rt::text size="sm">
                        {{ __('panel-app::livewire/profile-card.social.empty') }}
                    </x-he4rt::text>
                @endforelse
                <hr class="border-outline-light dark:border-outline-dark" />
            </div>

            <div class="flex flex-col gap-4">
                <x-he4rt::text class="text-text-high">
                    {{ __('panel-app::livewire/profile-card.preferences.title') }}
                </x-he4rt::text>
                @if ($candidate)
                    @if ($candidate->is_open_to_remote)
                        <x-he4rt::tag icon="heroicon-o-computer-desktop" variant="ghost">
                            {{ __('panel-app::livewire/profile-card.preferences.remote') }}
                        </x-he4rt::tag>
                    @endif

                    @if ($candidate->willing_to_relocate)
                        <x-he4rt::tag icon="heroicon-o-map-pin" variant="ghost">
                            {{ __('panel-app::livewire/profile-card.preferences.relocate') }}
                        </x-he4rt::tag>
                    @endif

                    @if ($candidate->expected_salary)
                        <x-he4rt::tag icon="heroicon-o-currency-dollar" variant="ghost">
                            {{ $candidate->expected_salary_currency }}
                            {{ number_format($candidate->expected_salary, 2) }}
                        </x-he4rt::tag>
                    @endif
                @else
                    <x-he4rt::text size="sm">
                        {{ __('panel-app::livewire/profile-card.preferences.empty') }}
                    </x-he4rt::text>
                @endif
            </div>
        </div>
    </div>
</aside>
