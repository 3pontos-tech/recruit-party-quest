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

    // Calculate total years of experience
    $totalMonths = 0;
    foreach ($workExperiences as $exp) {
        $endDate = $exp->is_currently_working_here ? now() : $exp->end_date;
        $months = $exp->start_date->diffInMonths($endDate);
        $totalMonths += $months;
    }
    $totalYears = round($totalMonths / 12, 1);

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
                <x-heroicon-o-briefcase class="h-5 w-5" />
            </div>
            <div>
                <h3 class="text-text-high text-lg font-semibold">Work Experience</h3>
                <p class="text-text-medium text-sm">Professional career history and achievements</p>
            </div>
        </div>
        @if ($hasExperience)
            <div class="flex items-center gap-2">
                @if ($currentJob)
                    <x-filament::badge size="sm" color="success">Currently Employed</x-filament::badge>
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
                    $duration = $startDate->diffInMonths($endDate);
                    $durationYears = floor($duration / 12);
                    $durationMonths = $duration % 12;

                    $durationText = '';
                    if ($durationYears > 0) {
                        $durationText = $durationYears . ' ' . Str::plural('year', $durationYears);
                        if ($durationMonths > 0) {
                            $durationText .= ' ' . $durationMonths . ' ' . Str::plural('month', $durationMonths);
                        }
                    } else {
                        $durationText = $durationMonths . ' ' . Str::plural('month', $durationMonths);
                    }
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
                                <x-heroicon-o-building-office-2
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
                                        <x-filament::badge color="success" size="sm">
                                            <x-heroicon-o-clock class="mr-1 h-3 w-3" />
                                            Current
                                        </x-filament::badge>
                                    @endif
                                </div>

                                {{-- Timeline and Duration --}}
                                <div class="text-text-medium mt-2 flex items-center gap-4 text-xs">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        {{ $startDate->format('M Y') }} -
                                        {{ $isCurrent ? 'Present' : $endDate->format('M Y') }}
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
                                            <x-filament::badge color="gray" size="sm" class="text-[10px]">
                                                {{ $skill }}
                                            </x-filament::badge>
                                        @endforeach

                                        @if (count($skills) > 8)
                                            <x-filament::badge color="gray" size="sm" class="text-[10px]">
                                                +{{ count($skills) - 8 }} more
                                            </x-filament::badge>
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
            <h4 class="text-text-high mb-3 text-sm font-semibold">Career Summary</h4>

            <div class="space-y-3">
                {{-- Career Timeline --}}
                <div>
                    <p class="text-text-medium mb-2 text-xs font-medium">Career Timeline</p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-text-high font-semibold">
                            {{ $workExperiences->min('start_date')?->format('Y') ?? 'N/A' }}
                        </span>
                        <span class="text-text-medium">→</span>
                        <span class="text-text-high font-semibold">
                            {{ $currentJob ? 'Present' : $workExperiences->max('end_date')?->format('Y') ?? 'N/A' }}
                        </span>
                        <span class="text-text-medium ml-2">({{ $totalYears }} years total)</span>
                    </div>
                </div>

                {{-- Companies Worked For --}}
                <div>
                    <p class="text-text-medium mb-2 text-xs font-medium">Companies</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($workExperiences->pluck('company_name')->unique() as $company)
                            <x-filament::badge size="sm" color="gray">
                                {{ $company }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- No Experience State --}}
        <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
            <x-heroicon-o-briefcase class="text-text-low mx-auto h-16 w-16" />
            <h4 class="text-text-high mt-4 text-lg font-medium">No Work Experience Listed</h4>
            <p class="text-text-medium mt-2 text-sm">
                This candidate hasn't added any work experience to their profile yet.
            </p>
        </div>
    @endif
</div>
