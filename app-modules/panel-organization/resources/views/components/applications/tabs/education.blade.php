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

<x-filament::section>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="bg-warning-100 text-warning-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                >
                    <x-heroicon-o-academic-cap class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">Education</h3>
                    <p class="text-text-medium text-sm">Academic background and qualifications</p>
                </div>
            </div>
            @if ($hasEducation)
                <div class="flex items-center gap-2">
                    @if ($currentEnrolled > 0)
                        <x-filament::badge color="info">{{ $currentEnrolled }} Current</x-filament::badge>
                    @endif

                    <x-filament::badge color="warning">
                        {{ $totalDegrees }} {{ Str::plural('degree', $totalDegrees) }}
                    </x-filament::badge>
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
                        $duration = $degree->start_date->diffInMonths($degree->end_date);
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
                                    <x-filament::badge color="{{ $color }}">
                                        {{ $degreeCategory }}
                                    </x-filament::badge>
                                    @if ($degree->is_enrolled)
                                        <x-filament::badge
                                            color="info"
                                            :icon="\Filament\Support\Icons\Heroicon::OutlinedClock"
                                        >
                                            {{-- <x-heroicon-o-clock class="w-3 h-3 mr-1" /> --}}
                                            In Progress
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge
                                            color="success"
                                            :icon="\Filament\Support\Icons\Heroicon::CheckCircle"
                                        >
                                            {{-- <x-heroicon-o-check-circle class="w-3 h-3 mr-1" /> --}}
                                            Completed
                                        </x-filament::badge>
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
                                <x-heroicon-o-building-library class="text-text-medium h-6 w-6" />
                            </div>
                            <div class="flex-1">
                                <p class="text-text-high text-base font-semibold">{{ $degree->institution }}</p>
                                <div class="text-text-medium mt-1 flex items-center gap-4 text-sm">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar-days class="h-4 w-4" />
                                        {{ $degree->start_date->format('M Y') }} -
                                        {{ $degree->is_enrolled ? 'Present' : $degree->end_date->format('M Y') }}
                                    </span>
                                    @if (! $degree->is_enrolled)
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-o-check-badge class="h-4 w-4" />
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
                                <x-filament::badge color="{{ $categoryColors[$category] ?? 'gray' }}">
                                    {{ $category }} ({{ $categoryDegrees->count() }})
                                </x-filament::badge>
                            @endforeach
                        </div>
                    </div>

                    {{-- Fields of Study --}}
                    <div>
                        <p class="text-text-medium mb-2 text-xs font-medium">Fields of Study</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($fieldsByCategory as $field => $fieldDegrees)
                                <x-filament::badge color="gray">
                                    {{ $field }}
                                    @if ($fieldDegrees->count() > 1)
                                        ({{ $fieldDegrees->count() }})
                                    @endif
                                </x-filament::badge>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- No Education State --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
                <x-heroicon-o-academic-cap class="text-text-low mx-auto h-16 w-16" />
                <h4 class="text-text-high mt-4 text-lg font-medium">No Education Information</h4>
                <p class="text-text-medium mt-2 text-sm">
                    This candidate hasn't added any education information to their profile yet.
                </p>
                <div class="mt-6">
                    <x-filament::button size="sm" color="gray" outlined icon="heroicon-o-plus" disabled>
                        Add Education
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament::section>
