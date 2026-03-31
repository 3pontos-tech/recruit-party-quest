@php
    use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
    use Illuminate\Support\Js;
@endphp

@props([
    'jobRequisition',
    'hasAction' => false,
])

@php
    $posting = $jobRequisition->post;
    $team = $jobRequisition->team;

    $hasApplied = false;
    if (auth()->check() && auth()->user()->candidate) {
        $hasApplied = $jobRequisition
            ->applications()
            ->where('candidate_id', auth()->user()->candidate->id)
            ->exists();
    }
@endphp

@if (! $posting)
    <div class="mx-auto w-full max-w-7xl py-12 text-center">
        <x-he4rt::text>{{ __('panel-app::filament.pages.job_description.no_posting') }}</x-he4rt::text>
    </div>
@else
    <div
        x-data="{
            showApplicationModal: false,
            hasApplied: @js($hasApplied),
        }"
        x-on:application-submitted.window="hasApplied = true; showApplicationModal = false"
        {{ $attributes->merge(['mx-auto w-full max-w-7xl space-y-8 py-8 lg:py-12']) }}
    >
        {{-- Job Header --}}
        <header
            class="border-outline-light dark:border-outline-dark grid grid-cols-1 items-start gap-8 border-b pb-8 md:grid-cols-[1fr_auto]"
        >
            <div class="flex flex-col items-start gap-6 sm:flex-row">
                {{-- Company Logo Placeholder --}}
                <x-he4rt::badge size="xl" icon="heroicon-o-briefcase" />

                <div class="space-y-3">
                    <div class="space-y-1">
                        <x-he4rt::heading level="1" size="lg" class="text-text-high leading-tight">
                            {{ $posting->title }}
                        </x-he4rt::heading>
                        <x-he4rt::text size="md" class="text-text-medium font-medium">
                            {{ $jobRequisition->is_confidential ? __('panel-app::filament.confidential.company_name') : $team->name }}
                        </x-he4rt::text>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-3 pt-1">
                        {{-- Work Model --}}
                        <x-he4rt::tag :icon="$jobRequisition->work_arrangement->getIcon()" variant="ghost">
                            {{ $jobRequisition->work_arrangement->getLabel() }}
                        </x-he4rt::tag>

                        {{-- Contract Type --}}
                        <x-he4rt::tag :icon="$jobRequisition->employment_type->getIcon()" variant="ghost">
                            {{ $jobRequisition->employment_type->getLabel() }}
                        </x-he4rt::tag>

                        {{-- Salary --}}
                        @if ($jobRequisition->show_salary_to_candidates)
                            <x-he4rt::tag icon="heroicon-o-currency-dollar" variant="ghost">
                                {{ $jobRequisition->salary_currency }}
                                {{ number_format($jobRequisition->salary_range_min, 0, ',', '.') }}
                                @if ($jobRequisition->salary_range_max)
                                        - {{ number_format($jobRequisition->salary_range_max, 0, ',', '.') }}
                                @endif
                            </x-he4rt::tag>
                        @endif

                        {{-- Seniority --}}
                        <x-he4rt::tag :icon="$jobRequisition->experience_level->getIcon()" variant="ghost">
                            {{ $jobRequisition->experience_level->getLabel() }}
                        </x-he4rt::tag>

                        {{-- Diversity Tag --}}
                        @if (! $jobRequisition->is_confidential && $team->is_disability_confident)
                            <x-he4rt::tag icon="heroicon-o-heart" variant="ghost">
                                {{ __('panel-app::filament.pages.job_description.diversity') }}
                            </x-he4rt::tag>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex w-full items-center gap-3">
                @if ($hasAction)
                    @guest
                        <x-he4rt::button variant="solid" class="w-full sm:w-auto" href="/login">
                            {{ __('panel-app::filament.pages.job_description.apply_button') }}
                        </x-he4rt::button>
                    @else
                        @php
                            $hasScreeningQuestions = $jobRequisition->screeningQuestions->isNotEmpty();
                        @endphp

                        {{-- Share Button --}}
                        <x-panel-app::jobs.share-job-button :job="$jobRequisition" size="md" />
                        {{-- Bookmark Button --}}
                        <livewire:panel-app.jobs.bookmark-job-button :job="$jobRequisition" size="md" />

                        <x-he4rt::button
                            variant="solid"
                            class="w-full sm:w-auto"
                            :disabled="$hasApplied"
                            @click="if (!hasApplied) {{ $hasScreeningQuestions ? 'showApplicationModal = true' : '$wire.applyDirectly()' }}"
                        >
                            <span x-show="!hasApplied">
                                {{ __('panel-app::filament.pages.job_description.apply_button') }}
                            </span>
                            <span x-show="hasApplied" x-cloak>
                                {{ __('panel-app::filament.pages.job_description.applied_button') }}
                            </span>
                        </x-he4rt::button>
                    @endguest
                @endif
            </div>
        </header>

        @php
            $description = $jobRequisition->post->description;
            $responsibilities = $jobRequisition->items->where('type', JobRequisitionItemTypeEnum::Responsibility);
            $requiredQualifications = $jobRequisition->items->where('type', JobRequisitionItemTypeEnum::RequiredQualification);
            $preferredQualifications = $jobRequisition->items->where('type', JobRequisitionItemTypeEnum::PreferredQualification);
            $benefits = $jobRequisition->items->where('type', JobRequisitionItemTypeEnum::Benefit);
        @endphp

        {{-- Job Content --}}
        <div class="mt-5 max-w-3xl space-y-10">
            {{-- About this job --}}
            <section class="space-y-4">
                <x-he4rt::heading level="2" size="sm" class="text-text-high">
                    {{ __('panel-app::filament.pages.job_description.about_this_job') }}
                </x-he4rt::heading>
                <div class="fi-prose text-text-medium max-w-none">
                    {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($description) }}
                </div>
            </section>

            {{-- Responsibilities --}}
            @if ($responsibilities->isNotEmpty())
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">
                        {{ __('panel-app::filament.pages.job_description.responsibilities') }}
                    </x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($responsibilities as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item->content }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Requirements --}}
            @if ($requiredQualifications->isNotEmpty())
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">
                        {{ __('panel-app::filament.pages.job_description.requirements') }}
                    </x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($requiredQualifications as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item->content }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Desirable skills --}}
            @if ($preferredQualifications->isNotEmpty())
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">
                        {{ __('panel-app::filament.pages.job_description.desirable_skills') }}
                    </x-he4rt::heading>
                    <ul class="list-inside list-disc space-y-2">
                        @foreach ($preferredQualifications as $item)
                            <li class="text-text-medium pl-1">
                                <span class="text-text-medium">{{ $item->content }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Benefits --}}
            @if ($benefits->isNotEmpty())
                <section class="space-y-4">
                    <x-he4rt::heading level="2" size="sm" class="text-text-high">
                        {{ __('panel-app::filament.pages.job_description.benefits') }}
                    </x-he4rt::heading>
                    <ul class="space-y-3">
                        @foreach ($benefits as $item)
                            <li class="flex items-start gap-3">
                                <x-he4rt::icon icon="heroicon-m-check-circle" class="text-primary mt-0.5" />
                                <x-he4rt::text size="md">{{ $item->content }}</x-he4rt::text>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        @if ($hasAction)
            @if ($jobRequisition->screeningQuestions->isNotEmpty())
                <x-he4rt::modal
                    show="showApplicationModal"
                    :title="__('panel-app::filament.pages.job_description.apply_modal_title', ['title' => $posting->title])"
                >
                    <livewire:screening.job-application-form :requisition="$jobRequisition" />
                </x-he4rt::modal>
            @endif
        @endif
    </div>
@endif
