<div
    x-data="{
        open: false,
        isMobile: window.innerWidth < 768,

        init() {
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 768
                if (! this.isMobile && this.open) {
                    this.open = false
                }
            })

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.close()
                }
            })
        },

        toggle() {
            this.open = ! this.open
            if (this.isMobile) {
                document.body.style.overflow = this.open ? 'hidden' : ''
            }
        },

        close() {
            this.open = false
            if (this.isMobile) {
                document.body.style.overflow = ''
            }
        },
    }"
    x-on:click.outside="! isMobile && (open = false)"
    class="relative"
>
    {{-- Badge Button --}}
    <button
        type="button"
        x-on:click="toggle()"
        class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 relative flex size-9 items-center justify-center rounded-lg border transition duration-200"
        :aria-expanded="open"
        aria-label="{{ __('panel-app::filament.components.saved_jobs_widget.aria_label') }}"
        data-test="saved-jobs-badge"
    >
        <x-he4rt::icon icon="heroicon-o-bookmark" class="size-5" />
        @if (count($savedJobs) > 0)
            <span
                class="bg-primary absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full text-[10px] font-bold text-white"
                data-test="saved-badge"
            >
                {{ count($savedJobs) }}
            </span>
        @endif
    </button>

    {{-- Desktop Dropdown --}}
    <div
        x-show="open && !isMobile"
        x-cloak
        x-transition:enter="transition duration-150 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-100 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="border-outline-light dark:border-outline-dark absolute top-full right-0 z-50 mt-2 w-80 origin-top-right rounded-xl border bg-black shadow-lg"
    >
        {{-- Header --}}
        <div class="border-outline-light dark:border-outline-dark flex items-center justify-between border-b px-4 py-3">
            <span class="text-text-high text-sm font-semibold">
                {{ __('panel-app::filament.components.saved_jobs_widget.title') }}
            </span>
            @if (count($savedJobs) > 0)
                <span class="text-text-medium text-xs font-medium">({{ count($savedJobs) }})</span>
            @endif
        </div>

        {{-- Content --}}
        <div class="max-h-[32rem] overflow-y-auto">
            @if (count($savedJobs) === 0)
                <div class="flex flex-col items-center gap-2 px-4 py-8 text-center">
                    <p class="text-text-medium text-sm font-medium">
                        {{ __('panel-app::filament.components.saved_jobs_widget.empty_title') }}
                    </p>
                    <p class="text-text-low text-xs">
                        {{ __('panel-app::filament.components.saved_jobs_widget.empty_description') }}
                    </p>
                </div>
            @else
                @foreach ($savedJobs as $job)
                    <div
                        wire:key="desktop-job-{{ $job['id'] }}"
                        class="border-outline-light dark:border-outline-dark border-b p-3 last:border-b-0"
                    >
                        <div class="space-y-2.5">
                            {{-- Title & Company --}}
                            <div class="space-y-0.5">
                                <x-he4rt::heading level="3" size="sm" class="text-text-high leading-tight">
                                    <a href="{{ $job['url'] }}" class="hover:text-primary transition">
                                        {{ $job['title'] }}
                                    </a>
                                </x-he4rt::heading>
                                <x-he4rt::text size="xs" class="text-text-medium">
                                    {{ $job['company'] }}
                                </x-he4rt::text>
                            </div>

                            {{-- Tags Row --}}
                            <div class="flex flex-wrap gap-2">
                                @if ($job['workArrangement'])
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-briefcase" size="xs">
                                        {{ $job['workArrangement'] }}
                                    </x-he4rt::tag>
                                @endif

                                @if ($job['employmentType'])
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-clock" size="xs">
                                        {{ $job['employmentType'] }}
                                    </x-he4rt::tag>
                                @endif

                                @if ($job['experienceLevel'])
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-chart-bar" size="xs">
                                        {{ $job['experienceLevel'] }}
                                    </x-he4rt::tag>
                                @endif

                                @if ($job['department'])
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-building-office-2" size="xs">
                                        {{ $job['department'] }}
                                    </x-he4rt::tag>
                                @endif

                                @if ($job['category'])
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-tag" size="xs">
                                        {{ $job['category'] }}
                                    </x-he4rt::tag>
                                @endif
                            </div>

                            {{-- Extras Row --}}
                            <div class="text-text-medium flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                                @if ($job['salaryRange'])
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-he4rt::icon icon="heroicon-o-currency-dollar" class="size-3.5" />
                                        {{ $job['salaryRange'] }}
                                    </span>
                                @endif

                                @if ($job['publishedAt'])
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-he4rt::icon icon="heroicon-o-calendar" class="size-3.5" />
                                        {{ $job['publishedAt'] }}
                                    </span>
                                @endif

                                <span class="inline-flex items-center gap-1.5">
                                    <x-he4rt::icon icon="heroicon-o-users" class="size-3.5" />
                                    {{ $job['applicationsCount'] }}
                                    {{ __('panel-app::filament.components.saved_jobs_widget.applications') }}
                                </span>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-2 pt-0.5">
                                <a
                                    href="{{ $job['url'] }}"
                                    class="hp-button hp-button-solid hp-button-size-xs hp-button-rounded-md w-auto flex-1 md:w-full"
                                >
                                    {{ __('panel-app::filament.components.saved_jobs_widget.view') }}
                                </a>
                                <button
                                    type="button"
                                    wire:click.stop="remove('{{ $job['id'] }}')"
                                    class="hp-button hp-button-outline hp-button-size-xs hp-button-rounded-md w-auto flex-1 md:w-full"
                                >
                                    {{ __('panel-app::filament.components.saved_jobs_widget.remove') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Mobile Sidebar Panel --}}
    <div x-show="open && isMobile" x-cloak class="fixed inset-0 z-50" data-test="sidebar-panel">
        {{-- Backdrop --}}
        <div
            x-show="open && isMobile"
            x-transition:enter="transition-opacity duration-300 ease-linear"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-300 ease-linear"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="close()"
            class="absolute inset-0 bg-black"
            data-test="backdrop"
        ></div>

        {{-- Sidebar --}}
        <div
            x-show="open && isMobile"
            x-transition:enter="transform transition duration-300 ease-in-out"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition duration-300 ease-in-out"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="bg-surface-primary dark:bg-surface-primary-dark absolute inset-y-0 right-0 flex w-full max-w-sm flex-col shadow-xl sm:w-96"
        >
            {{-- Header --}}
            <div
                class="border-outline-light dark:border-outline-dark flex items-center justify-between border-b px-4 py-4"
            >
                <div class="flex items-center gap-2">
                    <span class="text-text-high text-base font-semibold">
                        {{ __('panel-app::filament.components.saved_jobs_widget.title') }}
                    </span>
                    @if (count($savedJobs) > 0)
                        <span class="text-text-medium text-sm font-medium">({{ count($savedJobs) }})</span>
                    @endif
                </div>
                <button
                    type="button"
                    x-on:click="close()"
                    class="text-text-medium hover:text-text-high -mr-2 rounded-lg p-2 transition duration-200"
                    aria-label="{{ __('panel-app::filament.components.saved_jobs_widget.close') }}"
                >
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto">
                @if (count($savedJobs) === 0)
                    <div class="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                        <p class="text-text-medium text-base font-medium">
                            {{ __('panel-app::filament.components.saved_jobs_widget.empty_title') }}
                        </p>
                        <p class="text-text-low text-sm">
                            {{ __('panel-app::filament.components.saved_jobs_widget.empty_description') }}
                        </p>
                    </div>
                @else
                    <div class="divide-outline-light dark:divide-outline-dark divide-y">
                        @foreach ($savedJobs as $job)
                            <div wire:key="mobile-job-{{ $job['id'] }}" class="p-3">
                                <div class="space-y-2.5">
                                    {{-- Title & Company --}}
                                    <div class="space-y-0.5">
                                        <x-he4rt::heading level="3" size="sm" class="text-text-high leading-tight">
                                            <a
                                                href="{{ $job['url'] }}"
                                                x-on:click="close()"
                                                class="hover:text-primary transition"
                                            >
                                                {{ $job['title'] }}
                                            </a>
                                        </x-he4rt::heading>
                                        <x-he4rt::text size="xs" class="text-text-medium">
                                            {{ $job['company'] }}
                                        </x-he4rt::text>
                                    </div>

                                    {{-- Tags Row --}}
                                    <div class="flex flex-wrap gap-2">
                                        @if ($job['workArrangement'])
                                            <x-he4rt::tag variant="ghost" icon="heroicon-o-briefcase" size="xs">
                                                {{ $job['workArrangement'] }}
                                            </x-he4rt::tag>
                                        @endif

                                        @if ($job['employmentType'])
                                            <x-he4rt::tag variant="ghost" icon="heroicon-o-clock" size="xs">
                                                {{ $job['employmentType'] }}
                                            </x-he4rt::tag>
                                        @endif

                                        @if ($job['experienceLevel'])
                                            <x-he4rt::tag variant="ghost" icon="heroicon-o-chart-bar" size="xs">
                                                {{ $job['experienceLevel'] }}
                                            </x-he4rt::tag>
                                        @endif

                                        @if ($job['department'])
                                            <x-he4rt::tag variant="ghost" icon="heroicon-o-building-office-2" size="xs">
                                                {{ $job['department'] }}
                                            </x-he4rt::tag>
                                        @endif

                                        @if ($job['category'])
                                            <x-he4rt::tag variant="ghost" icon="heroicon-o-tag" size="xs">
                                                {{ $job['category'] }}
                                            </x-he4rt::tag>
                                        @endif
                                    </div>

                                    {{-- Extras Row --}}
                                    <div class="text-text-medium flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                                        @if ($job['salaryRange'])
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-he4rt::icon icon="heroicon-o-currency-dollar" class="size-3.5" />
                                                {{ $job['salaryRange'] }}
                                            </span>
                                        @endif

                                        @if ($job['publishedAt'])
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-he4rt::icon icon="heroicon-o-calendar" class="size-3.5" />
                                                {{ $job['publishedAt'] }}
                                            </span>
                                        @endif

                                        <span class="inline-flex items-center gap-1.5">
                                            <x-he4rt::icon icon="heroicon-o-users" class="size-3.5" />
                                            {{ $job['applicationsCount'] }}
                                            {{ __('panel-app::filament.components.saved_jobs_widget.applications') }}
                                        </span>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex gap-2 pt-0.5">
                                        <a
                                            href="{{ $job['url'] }}"
                                            x-on:click="close()"
                                            class="hp-button hp-button-solid hp-button-size-xs hp-button-rounded-md w-auto flex-1"
                                        >
                                            {{ __('panel-app::filament.components.saved_jobs_widget.view') }}
                                        </a>
                                        <button
                                            type="button"
                                            wire:click.stop="remove('{{ $job['id'] }}')"
                                            class="hp-button hp-button-outline hp-button-size-xs hp-button-rounded-md w-auto flex-1"
                                        >
                                            {{ __('panel-app::filament.components.saved_jobs_widget.remove') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
