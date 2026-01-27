@props([
    'record' => [],
])

@php
    $candidate = $record->candidate;

    // Mock evaluation data - in real implementation this would come from evaluations relationship
    $evaluations = [
        [
            'id' => 1,
            'interviewer' => 'Sarah Johnson',
            'type' => 'Phone Screening',
            'score' => 8.5,
            'max_score' => 10,
            'date' => now()->subDays(3),
            'status' => 'completed',
            'notes' => 'Strong communication skills, relevant experience aligns well with role requirements.',
        ],
        [
            'id' => 2,
            'interviewer' => 'Mike Chen',
            'type' => 'Technical Interview',
            'score' => 9.0,
            'max_score' => 10,
            'date' => now()->subDays(1),
            'status' => 'completed',
            'notes' => 'Excellent technical knowledge, solved coding problems efficiently.',
        ],
        [
            'id' => 3,
            'interviewer' => 'Team Lead',
            'type' => 'Team Interview',
            'score' => null,
            'max_score' => 10,
            'date' => now()->addDays(2),
            'status' => 'scheduled',
            'notes' => null,
        ],
    ];

    $completedEvaluations = collect($evaluations)->where('status', 'completed');
    $averageScore = $completedEvaluations->avg('score') ?? 0;
    $totalCompleted = $completedEvaluations->count();
    $totalEvaluations = count($evaluations);
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="text-text-high text-sm font-semibold">Evaluations</h3>
            <x-he4rt::tag size="sm" variant="outline">{{ $totalCompleted }}/{{ $totalEvaluations }}</x-he4rt::tag>
        </div>
        <x-he4rt::icon
            :icon="\Filament\Support\Icons\Heroicon::ClipboardDocumentCheck"
            size="sm"
            class="text-icon-medium"
        />
    </div>

    {{-- Overall Score --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3 text-center">
        <div class="space-y-1">
            <p class="text-text-medium text-xs font-semibold tracking-wider uppercase">Average Score</p>
            <div class="flex items-center justify-center gap-2">
                <span class="text-text-high text-2xl font-bold">{{ number_format($averageScore, 1) }}</span>
                <span class="text-text-medium text-sm">/10</span>
            </div>

            {{-- Score Bar --}}
            <div class="bg-elevation-01dp mt-2 h-2 w-full overflow-hidden rounded-full">
                <div
                    class="{{ $averageScore >= 8 ? 'bg-green-500' : ($averageScore >= 6 ? 'bg-yellow-500' : 'bg-red-500') }} h-full transition-all duration-500"
                    style="width: {{ ($averageScore / 10) * 100 }}%"
                ></div>
            </div>

            <p class="text-text-medium text-xs">
                Based on {{ $totalCompleted }} completed {{ Str::plural('evaluation', $totalCompleted) }}
            </p>
        </div>
    </div>

    {{-- Evaluations List --}}
    <div class="space-y-3">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">Interview History</h4>

        <div class="space-y-2">
            @foreach ($evaluations as $evaluation)
                <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
                    <div class="space-y-2">
                        {{-- Evaluation Header --}}
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <p class="text-text-high text-sm font-medium">{{ $evaluation['type'] }}</p>
                                <p class="text-text-medium text-xs">with {{ $evaluation['interviewer'] }}</p>
                            </div>

                            <div class="text-right">
                                @if ($evaluation['status'] === 'completed')
                                    <div class="flex items-center gap-1">
                                        <span class="text-text-high text-sm font-bold">
                                            {{ $evaluation['score'] }}
                                        </span>
                                        <span class="text-text-medium text-xs">/{{ $evaluation['max_score'] }}</span>
                                    </div>
                                    <x-he4rt::tag variant="solid" size="xs">Completed</x-he4rt::tag>
                                @elseif ($evaluation['status'] === 'scheduled')
                                    <x-he4rt::tag variant="outline" size="xs">Scheduled</x-he4rt::tag>
                                @endif
                            </div>
                        </div>

                        {{-- Date --}}
                        <div class="text-text-medium flex items-center gap-2 text-xs">
                            <x-he4rt::icon
                                :icon="\Filament\Support\Icons\Heroicon::CalendarDays"
                                size="sm"
                                class="text-icon-medium"
                            />
                            <span>
                                @if ($evaluation['status'] === 'completed')
                                    {{ $evaluation['date']->format('M j, Y') }}
                                    ({{ $evaluation['date']->diffForHumans() }})
                                @else
                                    {{ $evaluation['date']->format('M j, Y') }}
                                    ({{ $evaluation['date']->diffForHumans() }})
                                @endif
                            </span>
                        </div>

                        {{-- Score Bar (for completed evaluations) --}}
                        @if ($evaluation['status'] === 'completed')
                            <div class="bg-elevation-01dp h-1.5 w-full overflow-hidden rounded-full">
                                <div
                                    class="{{ $evaluation['score'] >= 8 ? 'bg-green-500' : ($evaluation['score'] >= 6 ? 'bg-yellow-500' : 'bg-red-500') }} h-full transition-all duration-500"
                                    style="width: {{ ($evaluation['score'] / $evaluation['max_score']) * 100 }}%"
                                ></div>
                            </div>
                        @endif

                        {{-- Notes Preview --}}
                        @if ($evaluation['notes'])
                            <div class="border-outline-low border-t pt-2">
                                <p class="text-text-medium line-clamp-2 text-xs">
                                    {{ $evaluation['notes'] }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Evaluation Stats --}}
    <div class="border-outline-low border-t pt-3">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-text-high text-sm font-bold">{{ $totalCompleted }}</p>
                <p class="text-text-medium text-xs">Completed</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">{{ $totalEvaluations - $totalCompleted }}</p>
                <p class="text-text-medium text-xs">Remaining</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">
                    {{ round(($totalCompleted / $totalEvaluations) * 100) }}%
                </p>
                <p class="text-text-medium text-xs">Progress</p>
            </div>
        </div>
    </div>

    {{-- Next Evaluation --}}
    @php
        $nextEvaluation = collect($evaluations)
            ->where('status', 'scheduled')
            ->first();
    @endphp

    @if ($nextEvaluation)
        <div class="bg-primary-50 border-primary-200 rounded-md border p-3">
            <div class="mb-2 flex items-center gap-2">
                <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::Clock" size="sm" class="text-primary" />
                <span class="text-primary text-xs font-semibold">Next Evaluation</span>
            </div>
            <p class="text-text-high text-sm font-medium">{{ $nextEvaluation['type'] }}</p>
            <p class="text-text-medium text-xs">{{ $nextEvaluation['date']->format('M j, Y \a\t g:i A') }}</p>
            <p class="text-text-medium text-xs">Interviewer: {{ $nextEvaluation['interviewer'] }}</p>
        </div>
    @endif

    {{-- Action Button --}}
    <x-he4rt::button
        size="sm"
        variant="outline"
        class="w-full"
        :icon="\Filament\Support\Icons\Heroicon::Plus"
        disabled
    >
        Schedule New Evaluation
    </x-he4rt::button>
</div>
