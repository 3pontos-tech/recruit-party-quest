<div
    x-data="{
        open: false,
        isMobile: window.innerWidth < 768,

        init() {
            // Initialize savedJobs store
            if (
                this.$store.savedJobs &&
                typeof this.$store.savedJobs.init === 'function'
            ) {
                this.$store.savedJobs.init()
            }

            // Update isMobile on resize
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 768
                // Close if switching from mobile to desktop while open
                if (! this.isMobile && this.open) {
                    this.open = false
                }
            })

            // Close on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.close()
                }
            })
        },

        toggle() {
            this.open = ! this.open
            // Prevent body scroll when mobile sidebar is open
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

        formatDate(isoDate) {
            if (! isoDate) return ''
            const date = new Date(isoDate)
            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            })
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
        <span
            x-show="$store.savedJobs.jobs.length > 0"
            x-cloak
            x-text="$store.savedJobs.jobs.length"
            class="bg-primary absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full text-[10px] font-bold text-white"
            data-test="saved-badge"
        ></span>
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
            <span
                x-show="$store.savedJobs.jobs.length > 0"
                x-cloak
                x-text="'(' + $store.savedJobs.jobs.length + ')'"
                class="text-text-medium text-xs font-medium"
            ></span>
        </div>

        {{-- Content --}}
        <div class="max-h-[32rem] overflow-y-auto">
            {{-- Empty State --}}
            <template x-if="$store.savedJobs.jobs.length === 0">
                <div class="flex flex-col items-center gap-2 px-4 py-8 text-center">
                    <p class="text-text-medium text-sm font-medium">
                        {{ __('panel-app::filament.components.saved_jobs_widget.empty_title') }}
                    </p>
                    <p class="text-text-low text-xs">
                        {{ __('panel-app::filament.components.saved_jobs_widget.empty_description') }}
                    </p>
                </div>
            </template>

            {{-- Job List --}}
            <template x-for="job in $store.savedJobs.getSortedJobs()" :key="job.id">
                <div class="border-outline-light dark:border-outline-dark border-b p-3 last:border-b-0">
                    <div class="space-y-2.5">
                        {{-- Title & Company --}}
                        <div class="space-y-0.5">
                            <x-he4rt::heading level="3" size="sm" class="text-text-high leading-tight">
                                <a :href="job.url" x-text="job.title" class="hover:text-primary transition"></a>
                            </x-he4rt::heading>
                            <x-he4rt::text size="xs" class="text-text-medium" x-text="job.company"></x-he4rt::text>
                        </div>

                        {{-- Tags Row (5 tags: Work Arrangement + Employment Type + Experience Level + Department + Category) --}}
                        <div class="flex flex-wrap gap-2">
                            <x-he4rt::tag
                                variant="ghost"
                                icon="heroicon-o-briefcase"
                                size="xs"
                                x-show="job.workArrangement"
                            >
                                <span x-text="job.workArrangement"></span>
                            </x-he4rt::tag>
                            <x-he4rt::tag variant="ghost" icon="heroicon-o-clock" size="xs" x-show="job.employmentType">
                                <span x-text="job.employmentType"></span>
                            </x-he4rt::tag>
                            <x-he4rt::tag
                                variant="ghost"
                                icon="heroicon-o-chart-bar"
                                size="xs"
                                x-show="job.experienceLevel"
                            >
                                <span x-text="job.experienceLevel"></span>
                            </x-he4rt::tag>
                            <x-he4rt::tag
                                variant="ghost"
                                icon="heroicon-o-building-office-2"
                                size="xs"
                                x-show="job.department"
                            >
                                <span x-text="job.department"></span>
                            </x-he4rt::tag>
                            <x-he4rt::tag variant="ghost" icon="heroicon-o-tag" size="xs" x-show="job.category">
                                <span x-text="job.category"></span>
                            </x-he4rt::tag>
                        </div>

                        {{-- Extras Row (Salary, Date, Applications) --}}
                        <div class="text-text-medium flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                            {{-- Salary --}}
                            <span x-show="job.salaryRange" class="inline-flex items-center gap-1.5">
                                <x-he4rt::icon icon="heroicon-o-currency-dollar" class="size-3.5" />
                                <span x-text="job.salaryRange"></span>
                            </span>

                            {{-- Published Date --}}
                            <span x-show="job.publishedAt" class="inline-flex items-center gap-1.5">
                                <x-he4rt::icon icon="heroicon-o-calendar" class="size-3.5" />
                                <span x-text="formatDate(job.publishedAt)"></span>
                            </span>

                            {{-- Applications Count --}}
                            <span
                                x-show="job.applicationsCount !== undefined"
                                class="inline-flex items-center gap-1.5"
                            >
                                <x-he4rt::icon icon="heroicon-o-users" class="size-3.5" />
                                <span
                                    x-text="
                                        job.applicationsCount +
                                            ' {{ __('panel-app::filament.components.saved_jobs_widget.applications') }}'
                                    "
                                ></span>
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-0.5">
                            <a
                                x-bind:href="job.url"
                                class="hp-button hp-button-solid hp-button-size-xs hp-button-rounded-md w-auto flex-1 md:w-full"
                            >
                                {{ __('panel-app::filament.components.saved_jobs_widget.view') }}
                            </a>
                            <button
                                type="button"
                                x-on:click.stop="$store.savedJobs.remove(job.id)"
                                class="hp-button hp-button-outline hp-button-size-xs hp-button-rounded-md w-auto flex-1 md:w-full"
                            >
                                {{ __('panel-app::filament.components.saved_jobs_widget.remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
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
                    <span
                        x-show="$store.savedJobs.jobs.length > 0"
                        x-cloak
                        x-text="'(' + $store.savedJobs.jobs.length + ')'"
                        class="text-text-medium text-sm font-medium"
                    ></span>
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
                {{-- Empty State --}}
                <template x-if="$store.savedJobs.jobs.length === 0">
                    <div class="flex h-full flex-col items-center justify-center gap-3 px-6 text-center">
                        <p class="text-text-medium text-base font-medium">
                            {{ __('panel-app::filament.components.saved_jobs_widget.empty_title') }}
                        </p>
                        <p class="text-text-low text-sm">
                            {{ __('panel-app::filament.components.saved_jobs_widget.empty_description') }}
                        </p>
                    </div>
                </template>

                {{-- Job List --}}
                <div class="divide-outline-light dark:divide-outline-dark divide-y">
                    <template x-for="job in $store.savedJobs.getSortedJobs()" :key="job.id">
                        <div class="p-3">
                            <div class="space-y-2.5">
                                {{-- Title & Company --}}
                                <div class="space-y-0.5">
                                    <x-he4rt::heading level="3" size="sm" class="text-text-high leading-tight">
                                        <a
                                            :href="job.url"
                                            x-on:click="close()"
                                            x-text="job.title"
                                            class="hover:text-primary transition"
                                        ></a>
                                    </x-he4rt::heading>
                                    <x-he4rt::text
                                        size="xs"
                                        class="text-text-medium"
                                        x-text="job.company"
                                    ></x-he4rt::text>
                                </div>

                                {{-- Tags Row (5 tags: Work Arrangement + Employment Type + Experience Level + Department + Category) --}}
                                <div class="flex flex-wrap gap-2">
                                    <x-he4rt::tag
                                        variant="ghost"
                                        icon="heroicon-o-briefcase"
                                        size="xs"
                                        x-show="job.workArrangement"
                                    >
                                        <span x-text="job.workArrangement"></span>
                                    </x-he4rt::tag>
                                    <x-he4rt::tag
                                        variant="ghost"
                                        icon="heroicon-o-clock"
                                        size="xs"
                                        x-show="job.employmentType"
                                    >
                                        <span x-text="job.employmentType"></span>
                                    </x-he4rt::tag>
                                    <x-he4rt::tag
                                        variant="ghost"
                                        icon="heroicon-o-chart-bar"
                                        size="xs"
                                        x-show="job.experienceLevel"
                                    >
                                        <span x-text="job.experienceLevel"></span>
                                    </x-he4rt::tag>
                                    <x-he4rt::tag
                                        variant="ghost"
                                        icon="heroicon-o-building-office-2"
                                        size="xs"
                                        x-show="job.department"
                                    >
                                        <span x-text="job.department"></span>
                                    </x-he4rt::tag>
                                    <x-he4rt::tag variant="ghost" icon="heroicon-o-tag" size="xs" x-show="job.category">
                                        <span x-text="job.category"></span>
                                    </x-he4rt::tag>
                                </div>

                                {{-- Extras Row (Salary, Date, Applications) --}}
                                <div class="text-text-medium flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
                                    {{-- Salary --}}
                                    <span x-show="job.salaryRange" class="inline-flex items-center gap-1.5">
                                        <x-he4rt::icon icon="heroicon-o-currency-dollar" class="size-3.5" />
                                        <span x-text="job.salaryRange"></span>
                                    </span>

                                    {{-- Published Date --}}
                                    <span x-show="job.publishedAt" class="inline-flex items-center gap-1.5">
                                        <x-he4rt::icon icon="heroicon-o-calendar" class="size-3.5" />
                                        <span x-text="formatDate(job.publishedAt)"></span>
                                    </span>

                                    {{-- Applications Count --}}
                                    <span
                                        x-show="job.applicationsCount !== undefined"
                                        class="inline-flex items-center gap-1.5"
                                    >
                                        <x-he4rt::icon icon="heroicon-o-users" class="size-3.5" />
                                        <span
                                            x-text="
                                                job.applicationsCount +
                                                    ' {{ __('panel-app::filament.components.saved_jobs_widget.applications') }}'
                                            "
                                        ></span>
                                    </span>
                                </div>

                                {{-- Actions --}}
                                <div class="flex gap-2 pt-0.5">
                                    <a
                                        x-bind:href="job.url"
                                        x-on:click="close()"
                                        class="hp-button hp-button-solid hp-button-size-xs hp-button-rounded-md w-auto flex-1"
                                    >
                                        {{ __('panel-app::filament.components.saved_jobs_widget.view') }}
                                    </a>
                                    <button
                                        type="button"
                                        x-on:click.stop="$store.savedJobs.remove(job.id)"
                                        class="hp-button hp-button-outline hp-button-size-xs hp-button-rounded-md w-auto flex-1"
                                    >
                                        {{ __('panel-app::filament.components.saved_jobs_widget.remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
