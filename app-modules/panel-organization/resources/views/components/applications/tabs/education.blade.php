@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    /** @var \He4rt\Candidates\Models\Candidate $candidate */
    /** @var \Illuminate\Support\Collection<int, \He4rt\Candidates\Models\Education> $degrees */
    $candidate = $record->candidate;
    $degrees = $candidate
        ->degrees()
        ->orderBy('end_date', 'desc')
        ->get();
    $hasEducation = $degrees->isNotEmpty();

    // Education statistics
    $totalDegrees = $degrees->count();
    $currentEnrolled = $degrees->where('is_enrolled', true)->count();
    $completedDegrees = $degrees->where('is_enrolled', false)->count();

    // Group degrees by level/type for better organization
    function categorizeDegree(string $degreeTitle): string
    {
        $degreeTypes = [
            'PhD' => ['PhD', 'Doctorate', 'Doctoral'],
            'Masters' => ['Masters', 'Master', 'MBA', 'MS', 'MA', 'MSc'],
            'Bachelors' => ['Bachelors', 'Bachelor', 'BS', 'BA', 'BSc'],
            'Associates' => ['Associates', 'Associate', 'AS', 'AA'],
            'Certificate' => ['Certificate', 'Certification', 'Diploma'],
        ];
        foreach ($degreeTypes as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($degreeTitle, $pattern) !== false) {
                    return $category;
                }
            }
        }
        return 'Other';
    }
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="bg-warning-100 text-warning-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                >
                    <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::AcademicCap" size="sm" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">Education</h3>
                    <p class="text-text-medium text-sm">Academic background and qualifications</p>
                </div>
            </div>
            @if ($hasEducation)
                <div class="flex items-center gap-2">
                    @if ($currentEnrolled > 0)
                        <x-he4rt::tag variant="solid">{{ $currentEnrolled }} Current</x-he4rt::tag>
                    @endif

                    <x-he4rt::tag variant="outline">
                        {{ $totalDegrees }} {{ Str::plural('Degree', $totalDegrees) }}
                    </x-he4rt::tag>
                </div>
            @endif
        </div>

        @if ($hasEducation)
            {{-- Education Timeline --}}
            <div class="space-y-4">
                @foreach ($degrees as $degree)
                    @php
                        $degreeCategory = categorizeDegree($degree->degree);
                        $isCompleted = ! $degree->is_enrolled;
                        $endDate = $degree->end_date ?? now();
                        $duration = $degree->start_date->diffInMonths($endDate);
                        $durationYears = floor($duration / 12);
                        $durationMonths = $duration % 12;

                        $categoryColors = [
                            'PhD' => 'purple',
                            'Masters' => 'blue',
                            'Bachelors' => 'green',
                            'Associates' => 'orange',
                            'Certificate' => 'yellow',
                            'Other' => 'gray',
                        ];

                        $color = $categoryColors[$degreeCategory] ?? 'gray';
                    @endphp

                    <div class="bg-elevation-02dp border-outline-low rounded-lg border p-6">
                        {{-- Degree Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <x-he4rt::tag variant="solid">
                                        {{ $degreeCategory }}
                                    </x-he4rt::tag>
                                    @if ($degree->is_enrolled)
                                        <x-he4rt::tag
                                            color="info"
                                            :icon="\Filament\Support\Icons\Heroicon::OutlinedClock"
                                        >
                                            {{-- <x-heroicon-o-clock class="w-3 h-3 mr-1" /> --}}
                                            In Progress
                                        </x-he4rt::tag>
                                    @else
                                        <x-he4rt::tag
                                            variant="solid"
                                            :icon="\Filament\Support\Icons\Heroicon::CheckCircle"
                                        >
                                            {{-- <x-heroicon-o-check-circle class="w-3 h-3 mr-1" /> --}}
                                            Completed
                                        </x-he4rt::tag>
                                    @endif
                                </div>

                                <h4 class="text-text-high text-lg font-semibold">{{ $degree->degree }}</h4>
                                <p class="text-text-medium text-base font-medium">{{ $degree->field_of_study }}</p>
                            </div>

                            <div class="text-right">
                                <p class="text-text-high text-sm font-semibold">
                                    {{ $degree->start_date->format('Y') }} -
                                    {{ $degree->is_enrolled ? 'Present' : $degree->end_date->format('Y') }}
                                </p>
                                <p class="text-text-medium text-xs">
                                    @if ($durationYears > 0)
                                        {{ $durationYears }} {{ Str::plural('year', $durationYears) }}
                                        @if ($durationMonths > 0)
                                                {{ $durationMonths }} {{ Str::plural('month', $durationMonths) }}
                                        @endif
                                    @else
                                        {{ $durationMonths }} {{ Str::plural('month', $durationMonths) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Institution Info --}}
                        <div class="border-outline-low mt-4 flex items-center gap-3 border-t pt-4">
                            <div
                                class="bg-elevation-01dp border-outline-low flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border"
                            >
                                <x-he4rt::icon
                                    :icon="\Filament\Support\Icons\Heroicon::BuildingLibrary"
                                    size="sm"
                                    class="text-text-medium"
                                />
                            </div>
                            <div class="flex-1">
                                <p class="text-text-high text-base font-semibold">{{ $degree->institution }}</p>
                                <div class="text-text-medium mt-1 flex items-center gap-4 text-sm">
                                    <span class="flex items-center gap-1">
                                        <x-he4rt::icon
                                            :icon="\Filament\Support\Icons\Heroicon::CalendarDays"
                                            size="xs"
                                        />
                                        {{ $degree->start_date->format('M Y') }} -
                                        {{ $degree->is_enrolled ? 'Present' : $degree->end_date->format('M Y') }}
                                    </span>
                                    @if (! $degree->is_enrolled)
                                        <span class="flex items-center gap-1">
                                            <x-he4rt::icon
                                                :icon="\Filament\Support\Icons\Heroicon::CheckBadge"
                                                size="xs"
                                            />
                                            Graduated
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Education Summary --}}
            <div class="bg-elevation-02dp border-outline-low rounded-lg border p-4">
                <h4 class="text-text-high mb-3 text-sm font-semibold">Education Summary</h4>

                @php
                    $degreesByCategory = $degrees->groupBy(function ($degree) {
                        return categorizeDegree($degree->degree);
                    });

                    $fieldsByCategory = $degrees->groupBy('field_of_study');
                @endphp

                <div class="space-y-3">
                    {{-- Degree Types --}}
                    <div>
                        <p class="text-text-medium mb-2 text-xs font-medium">Degree Types</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($degreesByCategory as $category => $categoryDegrees)
                                <x-he4rt::tag variant="solid">
                                    {{ $category }} ({{ $categoryDegrees->count() }})
                                </x-he4rt::tag>
                            @endforeach
                        </div>
                    </div>

                    {{-- Fields of Study --}}
                    <div>
                        <p class="text-text-medium mb-2 text-xs font-medium">Fields of Study</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($fieldsByCategory as $field => $fieldDegrees)
                                <x-he4rt::tag variant="outline">
                                    {{ $field }}
                                    @if ($fieldDegrees->count() > 1)
                                        ({{ $fieldDegrees->count() }})
                                    @endif
                                </x-he4rt::tag>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- No Education State --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::AcademicCap"
                    size="xl"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">No Education Information</h4>
                <p class="text-text-medium mt-2 text-sm">
                    This candidate hasn't added any education information to their profile yet.
                </p>
                <div class="mt-6">
                    <x-he4rt::button
                        size="sm"
                        variant="outline"
                        :icon="\Filament\Support\Icons\Heroicon::Plus"
                        disabled
                    >
                        Add Education
                    </x-he4rt::button>
                </div>
            </div>
        @endif
    </div>
</div>
