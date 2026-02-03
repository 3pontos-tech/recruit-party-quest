<div>
    <x-he4rt::card :interactive="false" class="flex flex-col gap-6">
        <x-slot name="header">
            <div class="flex items-start justify-between">
                <x-he4rt::heading level="2" size="sm">
                    {{ __('panel-app::livewire/user-profile-overview.title') }}
                </x-he4rt::heading>
                <x-he4rt::button variant="outline" size="sm" icon="heroicon-o-pencil-square">
                    {{ __('panel-app::livewire/user-profile-overview.edit_button') }}
                </x-he4rt::button>
            </div>
        </x-slot>

        {{-- User info section --}}
        <div class="flex items-start gap-4">
            <div
                class="bg-primary/20 text-primary flex h-16 w-16 shrink-0 items-center justify-center rounded-full text-lg font-semibold"
            >
                AB
            </div>
            <div class="min-w-0 flex-1">
                <x-he4rt::heading level="3" size="sm">Ana Beatriz Costa</x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-muted-foreground mt-0.5">
                    Senior Software Engineer | Full-Stack Developer
                </x-he4rt::text>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span
                        class="bg-primary/20 text-primary border-primary/30 inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                    >
                        Senior
                    </span>
                    <span
                        class="inline-flex items-center rounded-md border border-teal-500/30 bg-teal-500/20 px-2 py-0.5 text-xs font-medium text-teal-400"
                    >
                        Open to Remote
                    </span>
                    <span
                        class="inline-flex items-center rounded-md border border-blue-500/30 bg-blue-500/20 px-2 py-0.5 text-xs font-medium text-blue-400"
                    >
                        Willing to Relocate
                    </span>
                </div>
            </div>
        </div>

        {{-- Progress bar section --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="text-muted-foreground">
                    {{ __('panel-app::livewire/user-profile-overview.profile_completeness') }}
                </span>
                <span class="text-foreground font-medium">85%</span>
            </div>
            <div class="bg-primary/20 relative h-2 w-full overflow-hidden rounded-full">
                <div class="bg-primary h-full transition-all" style="width: 85%"></div>
            </div>
            <x-he4rt::text size="xs" class="text-muted-foreground">
                {{ __('panel-app::livewire/user-profile-overview.complete_profile_hint') }}
            </x-he4rt::text>
        </div>

        {{-- Contact Information --}}
        <div class="border-border space-y-3 border-t pt-2">
            <x-he4rt::heading level="4" size="xs">
                {{ __('panel-app::livewire/user-profile-overview.contact_information') }}
            </x-he4rt::heading>
            <div class="grid gap-2 text-sm">
                <div class="text-muted-foreground flex items-center gap-2">
                    <x-heroicon-o-envelope class="h-4 w-4 shrink-0" />
                    <span class="truncate">ana.costa@email.com</span>
                </div>
                <div class="text-muted-foreground flex items-center gap-2">
                    <x-heroicon-o-phone class="h-4 w-4 shrink-0" />
                    <span>+55 11 98765-4321</span>
                </div>
                <div class="text-muted-foreground flex items-center gap-2">
                    <x-heroicon-o-map-pin class="h-4 w-4 shrink-0" />
                    <span>America/Sao Paulo</span>
                </div>
            </div>
        </div>

        {{-- Links --}}
        <div class="border-border space-y-3 border-t pt-2">
            <x-he4rt::heading level="4" size="xs">
                {{ __('panel-app::livewire/user-profile-overview.links') }}
            </x-he4rt::heading>
            <div class="flex flex-wrap gap-2">
                <x-he4rt::button
                    variant="outline"
                    size="sm"
                    icon="heroicon-o-link"
                    href="https://linkedin.com/in/anabcosta"
                    target="_blank"
                >
                    LinkedIn
                </x-he4rt::button>
                <x-he4rt::button
                    variant="outline"
                    size="sm"
                    icon="heroicon-o-globe-alt"
                    href="https://anabcosta.dev"
                    target="_blank"
                >
                    Portfolio
                </x-he4rt::button>
            </div>
        </div>

        {{-- Job Preferences --}}
        <div class="border-border space-y-3 border-t pt-2">
            <x-he4rt::heading level="4" size="xs">
                {{ __('panel-app::livewire/user-profile-overview.job_preferences') }}
            </x-he4rt::heading>
            <div class="grid gap-2 text-sm">
                <div class="text-muted-foreground flex items-center gap-2">
                    <x-heroicon-o-currency-dollar class="h-4 w-4 shrink-0" />
                    <span>Expected: R$ 15.000 - 18.000/month</span>
                </div>
                <div class="text-muted-foreground flex items-center gap-2">
                    <x-heroicon-o-calendar class="h-4 w-4 shrink-0" />
                    <span>Available from 2/14/2025</span>
                </div>
            </div>
        </div>
    </x-he4rt::card>
</div>
