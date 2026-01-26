@props([
    'record' => [],
])

@php
    /** @var \He4rt\Applications\Models\Application $record  */
    $candidate = $record->candidate;
    $user = $candidate->user;
    $jobRequisition = $record->requisition;

    // Mock data for candidate insights - as requested, visual placeholders
    $skills = ['Laravel', 'Vue.js', 'PHP', 'JavaScript', 'MySQL', 'Docker'];
    $experience = '5+ years';
    $education = 'Computer Science, University Name';
    $location = 'San Francisco, CA';
    $availability = 'Available immediately';
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-text-high text-sm font-semibold">Candidate Snapshot</h3>
        <x-he4rt::icon icon="heroicon-o-user-circle" size="sm" class="text-icon-medium" />
    </div>

    {{-- Key Stats Grid --}}
    <div class="grid grid-cols-2 gap-4">
        {{-- Experience --}}
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <x-he4rt::icon icon="heroicon-o-briefcase" size="sm" class="text-icon-medium" />
                <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">Experience</span>
            </div>
            <p class="text-text-high text-sm font-semibold">{{ $experience }}</p>
        </div>

        {{-- Availability --}}
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <x-he4rt::icon icon="heroicon-o-clock" size="sm" class="text-icon-medium" />
                <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">Availability</span>
            </div>
            <p class="text-text-high text-sm font-semibold">{{ $availability }}</p>
        </div>
    </div>

    {{-- Location & Education --}}
    <div class="space-y-3">
        {{-- Location --}}
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <x-he4rt::icon icon="heroicon-o-map-pin" size="sm" class="text-icon-medium" />
                <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">Location</span>
            </div>
            <p class="text-text-high text-sm font-semibold">{{ $location }}</p>
        </div>

        {{-- Education --}}
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <x-he4rt::icon icon="heroicon-o-academic-cap" size="sm" class="text-icon-medium" />
                <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">Education</span>
            </div>
            <p class="text-text-high text-sm font-semibold">{{ $education }}</p>
        </div>
    </div>

    {{-- Skills --}}
    <div class="space-y-3">
        <div class="flex items-center gap-2">
            <x-he4rt::icon icon="heroicon-o-code-bracket" size="sm" class="text-icon-medium" />
            <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">Key Skills</span>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ($skills as $skill)
                <x-filament::badge size="sm" color="gray">
                    {{ $skill }}
                </x-filament::badge>
            @endforeach
        </div>
    </div>

    {{-- Application Summary --}}
    <div class="border-outline-low space-y-2 border-t pt-3">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">Application Summary</h4>

        <div class="space-y-2">
            {{-- Applied Position --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Position</span>
                <span class="text-text-high ml-2 truncate text-xs font-medium">
                    {{ $jobRequisition->post->title }}
                </span>
            </div>

            {{-- Department --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Department</span>
                <span class="text-text-high text-xs font-medium">{{ $jobRequisition->team->name }}</span>
            </div>

            {{-- Application Date --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Applied</span>
                <span class="text-text-high text-xs font-medium">{{ $record->created_at->format('M j, Y') }}</span>
            </div>

            {{-- Days in Process --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Days in Process</span>
                <span class="text-text-high text-xs font-medium">{{ $record->created_at->diffInDays() }} days</span>
            </div>
        </div>
    </div>

    {{-- Cover Letter Preview --}}
    @if ($record->cover_letter)
        <div class="border-outline-low border-t pt-3">
            <h4 class="text-text-medium mb-2 text-xs font-semibold tracking-wider uppercase">Cover Letter Preview</h4>
            <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
                <p class="text-text-medium line-clamp-3 text-xs">
                    {{ Str::limit($record->cover_letter, 120) }}
                </p>
                @if (strlen($record->cover_letter) > 120)
                    <x-filament::button size="xs" class="mt-2" color="gray" outlined disabled>
                        Read Full Letter
                    </x-filament::button>
                @endif
            </div>
        </div>
    @endif

    {{-- Quick Stats --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
        <div class="mb-2 flex items-center gap-2">
            <x-he4rt::icon icon="heroicon-o-chart-bar-square" size="sm" class="text-primary" />
            <span class="text-primary text-xs font-semibold">Quick Stats</span>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-text-high text-sm font-bold">87%</p>
                <p class="text-text-medium text-xs">Match Score</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">3</p>
                <p class="text-text-medium text-xs">Interviews</p>
            </div>
            <div>
                <p class="text-text-high text-sm font-bold">2</p>
                <p class="text-text-medium text-xs">Comments</p>
            </div>
        </div>
    </div>
</div>
