@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    /** @var \He4rt\Candidates\Models\Candidate $candidate */
    /** @var \He4rt\Candidates\Models\WorkExperience $workExperiences  */
    $candidate = $record->candidate;
    $workExperiences = $candidate
        ->workExperiences()
        ->orderBy('start_date', 'desc')
        ->get();
    $hasExperience = $workExperiences->isNotEmpty();

    $currentJob = $workExperiences->where('is_currently_working_here', true)->first();

    $totalExperienceTimeString = $candidate->totalExperienceFormatted;
@endphp

<x-filament::section icon="heroicon-o-briefcase" icon-color="info">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.work_experience.title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.work_experience.subtitle') }}
    </x-slot>

    @if ($hasExperience && $currentJob)
        <x-slot name="afterHeader">
            <x-he4rt::tag size="sm">
                {{ __('panel-organization::view.tabs.work_experience.currently_employed') }}
            </x-he4rt::tag>
        </x-slot>
    @endif

    @if ($hasExperience)
        {{-- Experience Timeline --}}
        <div class="space-y-4">
            @foreach ($workExperiences as $experience)
                @php
                    $isCurrent = $experience->is_currently_working_here;
                    $jobTitle = $experience->position ?? __('panel-organization::view.tabs.work_experience.professional_role_fallback');
                    $skills = $experience->metadata->skills;

                    $startDate = $experience->start_date;
                    $endDate = $isCurrent ? now() : $experience->end_date;

                    $durationText = $candidate->getExperienceDuration($experience);
                @endphp

                {{-- Experience Card --}}
                <x-filament::section :icon="\Filament\Support\Icons\Heroicon::BuildingOffice2" icon-color="info">
                    <x-slot name="heading">
                        {{ $jobTitle }}
                    </x-slot>
                    <x-slot name="description">
                        {{ $experience->company_name }}
                    </x-slot>
                    @if ($isCurrent)
                        <x-slot name="afterHeader">
                            <x-he4rt::tag size="sm">
                                <x-he4rt::icon
                                    :icon="\Filament\Support\Icons\Heroicon::Clock"
                                    size="xs"
                                    class="mr-1"
                                />
                                {{ __('panel-organization::view.tabs.work_experience.currently_employed') }}
                            </x-he4rt::tag>
                        </x-slot>
                    @endif

                    <div class="px-5">
                        <div class="flex items-start gap-4">
                            {{-- Job Details --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2"></div>

                                {{-- Timeline and Duration --}}
                                <div class="text-text-medium mt-2 flex items-center gap-4 text-base">
                                    <span class="flex items-center gap-1">
                                        <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::Calendar" size="xs" />
                                        {{ $startDate->format('M Y') }} -
                                        {{ $isCurrent ? __('panel-organization::view.tabs.work_experience.present') : $endDate?->format('M Y') ?? '—' }}
                                    </span>
                                    @if ($durationText !== null)
                                        <span>{{ $durationText }}</span>
                                    @endif
                                </div>

                                {{-- Description --}}
                                @if (! empty(trim($experience->description)))
                                    <div class="text-text-medium mt-4 text-base leading-7 whitespace-pre-line">
                                        {{ $experience->description }}
                                    </div>
                                @endif

                                {{-- Skills/Technologies --}}
                                @if (! empty($skills))
                                    <div class="mt-4 flex flex-wrap gap-1.5">
                                        @foreach (array_slice($skills, 0, 8) as $skill)
                                            <x-he4rt::tag variant="outline" size="sm" class="text-xs">
                                                {{ $skill }}
                                            </x-he4rt::tag>
                                        @endforeach

                                        @if (count($skills) > 8)
                                            <x-he4rt::tag variant="outline" size="sm" class="text-xs">
                                                {{ trans_choice('panel-organization::view.tabs.work_experience.more', count($skills) - 8, ['count' => count($skills) - 8]) }}
                                            </x-he4rt::tag>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Experience Summary --}}
        <x-filament::section class="mt-4">
            <x-slot name="heading">
                {{ __('panel-organization::view.tabs.work_experience.career_summary') }}
            </x-slot>
            <x-slot name="description">
                {{ __('panel-organization::view.tabs.work_experience.career_timeline') }}
            </x-slot>

            <div class="space-y-3">
                {{-- Career Timeline --}}
                <div>
                    <p class="text-text-medium mb-2 text-xs font-medium"></p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-text-high font-semibold">
                            {{ $workExperiences->min('start_date')?->format('Y') ?? 'N/A' }}
                        </span>
                        <span class="text-text-medium">→</span>
                        <span class="text-text-high font-semibold">
                            {{ $currentJob ? __('panel-organization::view.tabs.work_experience.present') : $workExperiences->max('end_date')?->format('Y') ?? 'N/A' }}
                        </span>
                        <span class="text-text-medium ml-2">({{ $totalExperienceTimeString }})</span>
                    </div>
                </div>

                {{-- Companies Worked For --}}
                <div>
                    <p class="text-text-medium mb-2 text-xs font-medium">
                        {{ __('panel-organization::view.tabs.work_experience.companies') }}
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($workExperiences->pluck('company_name')->unique() as $company)
                            <x-he4rt::tag size="sm" variant="outline">
                                {{ $company }}
                            </x-he4rt::tag>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-filament::section>
    @else
        {{-- No Experience State --}}
        <x-filament::section :secondary="true">
            <div class="py-4 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::Briefcase"
                    size="md"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.work_experience.no_experience') }}
                </h4>
                <p class="text-text-medium mt-2 text-sm">
                    {{ __('panel-organization::view.tabs.work_experience.no_experience_text') }}
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
