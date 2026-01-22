@props([
    'jobRequisition',
])

@php
    $posting = $jobRequisition->post;
    $team = $jobRequisition->team;
@endphp

@if (! $posting)
    <div class="mx-auto w-full max-w-7xl py-12 text-center">
        <x-he4rt::text>Job posting details are currently unavailable.</x-he4rt::text>
    </div>
@else
    <div
        x-data="{ showApplicationModal: false }"
        {{ $attributes->merge(['mx-auto w-full max-w-7xl space-y-8 py-8 lg:py-12']) }}
    >
        {{-- Job Header --}}
        <header class="border-outline-low grid grid-cols-1 items-start gap-8 border-b pb-8 md:grid-cols-[1fr_auto]">
            <div class="flex flex-col items-start gap-6 sm:flex-row">
                {{-- Company Logo Placeholder --}}
                <div
                    class="bg-elevation-01dp border-outline-low flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border"
                >
                    <x-he4rt::icon icon="heroicon-o-briefcase" class="text-icon-medium h-8 w-8" />
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <x-he4rt::heading level="1" size="lg" class="text-text-high leading-tight">
                            {{ $posting->title }}
                        </x-he4rt::heading>
                        <x-he4rt::text size="md" class="text-text-medium font-medium">
                            {{ $team->name }}
                        </x-he4rt::text>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-3 pt-1">
                        {{-- Location - Fallback to Remote if not available --}}
                        <div class="text-text-medium flex items-center gap-2 text-sm">
                            <x-he4rt::icon icon="heroicon-o-map-pin" size="sm" class="text-icon-medium" />
                            <span>Remote</span>
                        </div>

                        {{-- Work Model --}}
                        <div class="text-text-medium flex items-center gap-2 text-sm">
                            <x-he4rt::icon
                                :icon="$jobRequisition->work_arrangement->getIcon()"
                                size="sm"
                                class="text-icon-medium"
                            />
                            <span>{{ $jobRequisition->work_arrangement->getLabel() }}</span>
                        </div>

                        {{-- Contract Type --}}
                        <div class="text-text-medium flex items-center gap-2 text-sm">
                            <x-he4rt::icon
                                :icon="$jobRequisition->employment_type->getIcon()"
                                size="sm"
                                class="text-icon-medium"
                            />
                            <span>{{ $jobRequisition->employment_type->getLabel() }}</span>
                        </div>

                        {{-- Salary --}}
                        @if ($jobRequisition->salary_range_min)
                            <div class="text-text-medium flex items-center gap-2 text-sm">
                                <x-he4rt::icon icon="heroicon-o-currency-dollar" size="sm" class="text-icon-medium" />
                                <span>
                                    {{ $jobRequisition->salary_currency }}
                                    {{ number_format($jobRequisition->salary_range_min, 0, ',', '.') }}
                                    @if ($jobRequisition->salary_range_max)
                                            - {{ number_format($jobRequisition->salary_range_max, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        {{-- Seniority --}}
                        <div class="text-text-medium flex items-center gap-2 text-sm">
                            <x-he4rt::icon
                                :icon="$jobRequisition->experience_level->getIcon()"
                                size="sm"
                                class="text-icon-medium"
                            />
                            <span>{{ $jobRequisition->experience_level->getLabel() }}</span>
                        </div>

                        {{-- Diversity Tag --}}
                        @if ($posting->is_disability_confident)
                            <div class="text-text-medium flex items-center gap-2 text-sm">
                                <x-he4rt::icon icon="heroicon-o-heart" size="sm" class="text-icon-medium" />
                                <span>Diversity</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex w-full flex-col items-center gap-3 sm:flex-row md:w-auto">
                <x-he4rt::button
                    variant="solid"
                    class="w-full sm:w-auto"
                    @click="{{ $jobRequisition->screeningQuestions->isNotEmpty() ? 'showApplicationModal = true' : '' }}"
                >
                    Apply for job
                </x-he4rt::button>
                <div class="flex w-full justify-center gap-3 sm:w-auto">
                    <x-he4rt::button variant="outline" icon="heroicon-o-bookmark" class="flex-1" />
                    <x-he4rt::button variant="outline" icon="heroicon-o-share" class="flex-1" />
                </div>
            </div>
        </header>

        {{-- Job Content --}}
        <div class="mt-5 max-w-3xl space-y-10">
            {{-- About this job --}}
            <section class="space-y-4">
                <x-he4rt::heading level="2" size="sm" class="text-text-high">About this job</x-he4rt::heading>
                <div class="space-y-4">
                    <x-he4rt::text size="md">
                        {{ $posting->summary }}
                    </x-he4rt::text>
                    @if (is_array($posting->description))
                        @foreach ($posting->description as $paragraph)
                            <x-he4rt::text size="md">
                                {{ $paragraph }}
                            </x-he4rt::text>
                        @endforeach
                    @endif
                </div>
            </section>

            {{-- Responsibilities --}}
            @if (is_array($posting->responsibilities) && count($posting->responsibilities) > 0)
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">Responsibilities</x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($posting->responsibilities as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Requirements --}}
            @if (is_array($posting->required_qualifications) && count($posting->required_qualifications) > 0)
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">Requirements</x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($posting->required_qualifications as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Desirable skills --}}
            @if (is_array($posting->preferred_qualifications) && count($posting->preferred_qualifications) > 0)
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">Desirable skills</x-he4rt::heading>
                    <ul class="list-inside list-disc space-y-2">
                        @foreach ($posting->preferred_qualifications as $item)
                            <li class="text-text-medium pl-1">
                                <span class="text-text-medium">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Benefits --}}
            @if (is_array($posting->benefits) && count($posting->benefits) > 0)
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">Benefits</x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($posting->benefits as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Additional Information --}}
            @if ($posting->about_team)
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">
                        Additional information
                    </x-he4rt::heading>
                    <x-he4rt::text size="md">
                        {{ $posting->about_team }}
                    </x-he4rt::text>
                </section>
            @endif
        </div>

        @if ($jobRequisition->screeningQuestions->isNotEmpty())
            <x-he4rt::modal show="showApplicationModal" title="Apply for {{ $posting->title }}">
                <livewire:screening.job-application-form :requisition="$jobRequisition" />
            </x-he4rt::modal>
        @endif
    </div>
@endif
