@php
    use Filament\Support\Icons\Heroicon;
@endphp

@props([
    'record',
])

@php
    /** @var \Illuminate\Support\Collection<int,\He4rt\Candidates\Models\Skill> $skills */
    /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $jobRequisition  */
    /** @var \He4rt\Candidates\Models\Candidate $candidate */
    /** @var \He4rt\Users\User $user */

    $candidate = $record->candidate;
    $user = $candidate->user;
    $jobRequisition = $record->requisition;
    $skills = $candidate->skills;

    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($name) => mb_strtoupper(mb_substr($name, 0, 1)))
        ->implode('');

    $experienceTimeString = $candidate->totalExperienceFormatted;
    $availabilityDate = $candidate->availability_date;

    if ($availabilityDate === null) {
        $availability = __('panel-organization::view.status.immediate');
    } else {
        $availability = $availabilityDate->isPast()
            ? __('panel-organization::view.status.immediate')
            : __('panel-organization::view.status.available_from', ['date' => $availabilityDate->format('d M Y')]);
    }
    $location = $candidate->address?->label;

    $education = $candidate->degrees->map(fn ($e) => "{$e->institution} — {$e->degree}")->implode(' | ');
@endphp

<x-filament::section>
    {{-- Candidate Header --}}
    <header class="flex flex-col gap-2 md:grid-cols-[1fr_auto]">
        {{-- Tracking Code --}}
        <div class="flex items-end justify-end gap-2">
            <span class="text-text-medium font-mono text-sm">{{ $record->tracking_code }}</span>
        </div>

        <div class="border-text-low/20 mb-4 flex justify-between border-b pb-4">
            <div class="flex flex-col items-center gap-6 sm:flex-row">
                {{-- Profile Photo Placeholder --}}
                <div class="flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-full">
                    <img src="{{ $user->getFilamentAvatarUrl() }}" class="h-32 w-32" alt="{{ $user->name }}" />
                </div>

                <div class="flex flex-col justify-evenly space-y-3">
                    {{-- Candidate Name --}}
                    <x-he4rt::heading level="1" size="lg" class="text-text-high leading-tight">
                        {{ $user->name }}
                    </x-he4rt::heading>

                    {{-- Professional Title --}}
                    @if ($candidate->headline)
                        <x-he4rt::text size="md" class="text-text-medium font-medium">
                            {{ $candidate->headline }}
                        </x-he4rt::text>
                    @endif

                    <div>
                        <x-he4rt::tag>
                            {{ $record->status->getLabel() }}
                        </x-he4rt::tag>
                        <x-he4rt::tag>
                            {{ $record->source->getLabel() }}
                        </x-he4rt::tag>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end justify-evenly pt-1">
                {{-- Email --}}
                <div class="flex items-center gap-2">
                    <x-he4rt::icon :icon="Heroicon::Envelope" size="sm" class="text-icon-medium" />
                    <span>{{ $user->email }}</span>
                </div>

                {{-- Phone --}}
                @if ($candidate->phone_number)
                    <div class="flex items-center gap-2">
                        <x-he4rt::icon :icon="Heroicon::Phone" size="sm" class="text-icon-medium" />
                        <span>{{ $candidate->phone_number }}</span>
                    </div>
                @endif

                <div class="flex gap-2">
                    @foreach ($user->links as $link)
                        <x-he4rt::tag
                            :icon="$link->icon"
                            :href="$link->url"
                            target="_blank"
                            class="hover:text-blue-400"
                        >
                            {{ $link->name }}
                        </x-he4rt::tag>
                    @endforeach
                </div>
            </div>
        </div>
        {{-- Application Info --}}
        <div class="border-border border-outline-low w-full">
            <div class="grid w-full grid-cols-1 gap-x-10 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::Briefcase" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.position') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $jobRequisition->post->title }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::BuildingOffice2" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.department') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $jobRequisition->team->name }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::Calendar" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.applied') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $record->created_at->format('M j, Y') }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::Briefcase" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.experience') }}
                    </span>
                    <p class="text-text-high ml-5 text-sm font-semibold">{{ $experienceTimeString }}</p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::Clock" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.availability') }}
                    </span>
                    <p class="text-text-high ml-5 text-sm font-semibold">{{ $availability }}</p>
                </div>

                @if ($location)
                    <div>
                        <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500">
                            <x-he4rt::icon :icon="Heroicon::MapPin" size="sm" class="text-icon-medium" />
                            {{ __('panel-organization::view.candidate_header.location') }}
                        </span>
                        <p class="text-text-high ml-5 text-sm font-semibold">{{ $location }}</p>
                    </div>
                @endif

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500">
                        <x-he4rt::icon :icon="Heroicon::AcademicCap" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.education') }}
                    </span>
                    <p class="text-text-high ml-5 line-clamp-3 text-sm font-semibold" title="{{ $education }}">
                        {{ $education }}
                    </p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                <div class="flex items-center gap-2">
                    <x-he4rt::icon :icon="Heroicon::CodeBracket" size="sm" class="text-icon-medium" />
                    <span class="text-text-medium text-xs font-semibold tracking-wide">
                        {{ __('panel-organization::view.candidate_header.key_skills') }}
                    </span>
                </div>

                <div class="ml-5">
                    @foreach ($skills->take(7) as $skill)
                        <x-he4rt::tag size="sm">
                            {{ $skill->name }}
                        </x-he4rt::tag>
                    @endforeach
                </div>
            </div>
        </div>
    </header>
</x-filament::section>
