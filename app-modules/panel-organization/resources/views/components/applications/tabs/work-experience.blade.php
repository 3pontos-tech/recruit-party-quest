@props(['record' => []])

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

    $experience = $candidate->totalExperienceTime();

    $years = $experience['years'];
    $months = $experience['months'];

    $totalExperienceTimeString = $candidate->totalExperienceFormatted;

    // Helper function to extract job title from description
    function extractJobTitle($description, $metadata = null): string
    {
        if ($metadata && isset($metadata['position'])) {
            return $metadata['position'];
        }

        // Extract first line as potential job title
        $lines = explode("\n", trim($description));
        $firstLine = trim($lines[0]);

        // If first line looks like a title (short and doesn't start with bullet), use it
        if (strlen($firstLine) <= 60 && ! preg_match('/^[•\-\*]/', $firstLine)) {
            return $firstLine;
        }

        return 'Professional Role'; // Fallback
    }

    // Helper function to extract skills from metadata or description
    function extractSkills($metadata, $description): array
    {
        $skills = [];

        if ($metadata) {
            if (isset($metadata['skills']) && is_array($metadata['skills'])) {
                $skills = array_merge($skills, $metadata['skills']);
            }
            if (isset($metadata['technologies']) && is_array($metadata['technologies'])) {
                $skills = array_merge($skills, $metadata['technologies']);
            }
        }

        // If no skills in metadata, try to extract from description
        if (empty($skills)) {
            // Look for common tech keywords in description
            $commonTech = ['PHP', 'Laravel', 'JavaScript', 'React', 'Vue', 'Node.js', 'Python', 'Java', 'MySQL', 'PostgreSQL', 'MongoDB', 'AWS', 'Docker', 'Kubernetes', 'Git'];
            foreach ($commonTech as $tech) {
                if (stripos($description, $tech) !== false) {
                    $skills[] = $tech;
                }
            }
        }

        return array_unique($skills);
    }

    // Helper function to format job description
    function formatJobDescription($description): string
    {
        $lines = explode("\n", trim($description));

        // Remove first line if it looks like a job title
        if (count($lines) > 1) {
            $firstLine = trim($lines[0]);
            if (strlen($firstLine) <= 60 && ! preg_match('/^[•\-\*]/', $firstLine)) {
                array_shift($lines);
            }
        }

        return implode("\n", $lines);
    }
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::Briefcase" size="sm" />
            </div>
            <div>
                <h3 class="text-text-high text-lg font-semibold">
                    {{ __('panel-organization::view.tabs.work_experience.title') }}
                </h3>
                <p class="text-text-medium text-sm">
                    {{ __('panel-organization::view.tabs.work_experience.subtitle') }}
                </p>
            </div>
        </div>
        @if ($hasExperience)
            <div class="flex items-center gap-2">
                @if ($currentJob)
                    <x-he4rt::tag size="sm" variant="solid">
                        {{ __('panel-organization::view.tabs.work_experience.currently_employed') }}
                    </x-he4rt::tag>
                @endif
            </div>
        @endif
    </div>

    @if ($hasExperience)
        {{-- Experience Timeline --}}
        <div class="space-y-4">
            @foreach ($workExperiences as $experience)
                @php
                    $isCurrent = $experience->is_currently_working_here;
                    $jobTitle = extractJobTitle($experience->description, $experience->metadata);
                    $skills = extractSkills($experience->metadata, $experience->description);
                    $formattedDescription = formatJobDescription($experience->description);

                    $startDate = $experience->start_date;
                    $endDate = $isCurrent ? now() : $experience->end_date;

                    $durationText = $candidate->getExperienceDuration($experience);
                @endphp

                {{-- Experience Card --}}
                <div
                    class="{{ $isCurrent ? 'border-primary/30 bg-primary/5' : 'bg-surface-01dp border-outline-low' }} flex flex-col gap-6 rounded-xl border py-6 shadow-sm"
                >
                    <div class="px-5">
                        <div class="flex items-start gap-4">
                            {{-- Company Icon --}}
                            <div
                                class="{{ $isCurrent ? 'bg-primary/20' : 'bg-muted' }} flex h-12 w-12 shrink-0 items-center justify-center rounded-lg"
                            >
                                <x-he4rt::icon
                                    :icon="\Filament\Support\Icons\Heroicon::BuildingOffice2"
                                    size="sm"
                                    class="h-6 w-6 {{ $isCurrent ? 'text-primary' : 'text-muted-foreground' }}"
                                />
                            </div>

                            {{-- Job Details --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="text-text-high text-base font-semibold">{{ $jobTitle }}</h3>
                                        <p class="text-text-medium text-sm">{{ $experience->company_name }}</p>
                                    </div>

                                    @if ($isCurrent)
                                        <x-he4rt::tag variant="solid" size="sm">
                                            <x-he4rt::icon
                                                :icon="\Filament\Support\Icons\Heroicon::Clock"
                                                size="xs"
                                                class="mr-1"
                                            />
                                            {{ __('panel-organization::view.tabs.work_experience.currently_employed') }}
                                        </x-he4rt::tag>
                                    @endif
                                </div>

                                {{-- Timeline and Duration --}}
                                <div class="text-text-medium mt-2 flex items-center gap-4 text-xs">
                                    <span class="flex items-center gap-1">
                                        <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::Calendar" size="xs" />
                                        {{ $startDate->format('M Y') }} -
                                        {{ $isCurrent ? __('panel-organization::view.tabs.work_experience.present') : $endDate->format('M Y') }}
                                    </span>
                                    <span>{{ $durationText }}</span>
                                </div>

                                {{-- Description --}}
                                @if (! empty(trim($formattedDescription)))
                                    <div class="text-text-medium mt-4 text-sm leading-relaxed whitespace-pre-line">
                                        {{ $formattedDescription }}
                                    </div>
                                @endif

                                {{-- Skills/Technologies --}}
                                @if (! empty($skills))
                                    <div class="mt-4 flex flex-wrap gap-1.5">
                                        @foreach (array_slice($skills, 0, 8) as $skill)
                                            <x-he4rt::tag variant="outline" size="sm" class="text-[10px]">
                                                {{ $skill }}
                                            </x-he4rt::tag>
                                        @endforeach

                                        @if (count($skills) > 8)
                                            <x-he4rt::tag variant="outline" size="sm" class="text-[10px]">
                                                {{ trans_choice('panel-organization::view.tabs.work_experience.more', count($skills) - 8, ['count' => count($skills) - 8]) }}
                                            </x-he4rt::tag>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Experience Summary --}}
        <div class="bg-elevation-02dp border-outline-low rounded-lg border p-4">
            <h4 class="text-text-high mb-3 text-sm font-semibold">
                {{ __('panel-organization::view.tabs.work_experience.career_summary') }}
            </h4>

            <div class="space-y-3">
                {{-- Career Timeline --}}
                <div>
                    <p class="text-text-medium mb-2 text-xs font-medium">
                        {{ __('panel-organization::view.tabs.work_experience.career_timeline') }}
                    </p>
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
        </div>
    @else
        {{-- No Experience State --}}
        <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
            <x-he4rt::icon
                :icon="\Filament\Support\Icons\Heroicon::Briefcase"
                size="xl"
                class="text-text-low mx-auto"
            />
            <h4 class="text-text-high mt-4 text-lg font-medium">
                {{ __('panel-organization::view.tabs.work_experience.no_experience') }}
            </h4>
            <p class="text-text-medium mt-2 text-sm">
                {{ __('panel-organization::view.tabs.work_experience.no_experience_text') }}
            </p>
        </div>
    @endif
</div>
