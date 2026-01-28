@php
    use Filament\Support\Icons\Heroicon;
@endphp

@props(['record' => []])

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
    $location = $candidate->address->label;

    $education = $candidate->degrees->map(fn ($e) => "{$e->institution} — {$e->degree}")->implode(' | ');
@endphp

<x-filament::section>
    {{-- Candidate Header --}}
    <header class="flex flex-col gap-2 md:grid-cols-[1fr_auto]">
        {{-- Tracking Code --}}
        <div class="flex items-end justify-end gap-2">
            <span class="text-text-medium font-mono text-sm">{{ $record->tracking_code }}</span>
        </div>

        <div class="flex justify-between">
            <div class="flex flex-col items-center gap-6 sm:flex-row">
                {{-- Profile Photo Placeholder --}}
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full">
                    <img
                        src="https://placehold.co/80x80/16a34a/ffffff?text={{ $initials }}"
                        alt="{{ __('panel-organization::view.candidate_header.profile_image_alt') }}"
                        class="h-20 w-20"
                    />
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
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
                    </div>
                    <div>
                        <x-he4rt::tag variant="outline">
                            {{ $record->status->getLabel() }}
                        </x-he4rt::tag>
                        <x-he4rt::tag variant="outline">
                            {{ $record->source->getLabel() }}
                        </x-he4rt::tag>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-x-6 gap-y-3 pt-1">
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
                    {{-- LinkedIn --}}
                    @if ($candidate->linkedin_url)
                        <x-he4rt::tag :icon="Heroicon::Link" variant="solid" class="bg-black p-2">
                            <a href="{{ $candidate->linkedin_url }}" target="_blank" class="hover:text-blue-400">
                                {{ __('panel-organization::view.candidate_header.linkedin') }}
                            </a>
                        </x-he4rt::tag>
                    @endif

                    {{-- Portfolio --}}
                    @if ($candidate->portfolio_url)
                        <x-he4rt::tag :icon="Heroicon::GlobeAlt" variant="solid" class="bg-black p-2">
                            <a href="{{ $candidate->portfolio_url }}" target="_blank" class="hover:text-blue-400">
                                {{ __('panel-organization::view.candidate_header.portfolio') }}
                            </a>
                        </x-he4rt::tag>
                    @endif
                </div>
            </div>
        </div>
        {{-- Application Info --}}
        <div class="border-border border-outline-low w-full">
            <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::Briefcase" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.position') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $jobRequisition->post->title }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::BuildingOffice2" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.department') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $jobRequisition->team->name }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex items-center gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::Calendar" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.applied') }}
                    </span>
                    <p class="text-foreground ml-5 text-sm font-medium">
                        {{ $record->created_at->format('M j, Y') }}
                    </p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::Briefcase" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.experience') }}
                    </span>
                    <p class="text-text-high ml-5 text-sm font-semibold">{{ $experienceTimeString }}</p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::Clock" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.availability') }}
                    </span>
                    <p class="text-text-high ml-5 text-sm font-semibold">{{ $availability }}</p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::MapPin" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.location') }}
                    </span>
                    <p class="text-text-high ml-5 text-sm font-semibold">{{ $location }}</p>
                </div>

                <div>
                    <span class="mb-1 flex gap-1 text-xs tracking-wider text-gray-500 uppercase">
                        <x-he4rt::icon :icon="Heroicon::AcademicCap" size="sm" class="text-icon-medium" />
                        {{ __('panel-organization::view.candidate_header.education') }}
                    </span>
                    <p class="text-text-high ml-5 line-clamp-2 text-sm font-semibold" title="{{ $education }}">
                        {{ $education }}
                    </p>
                </div>

                <div class="space-y-3 sm:col-span-2 lg:col-span-4">
                    <div class="flex items-center gap-2">
                        <x-he4rt::icon :icon="Heroicon::CodeBracket" size="sm" class="text-icon-medium" />
                        <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">
                            {{ __('panel-organization::view.candidate_header.key_skills') }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <x-he4rt::tag size="sm" variant="outline">
                                {{ $skill->name }}
                            </x-he4rt::tag>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </header>
</x-filament::section>
