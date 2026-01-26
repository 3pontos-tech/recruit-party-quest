@props(['record' => []])

@php
    $candidate = $record->candidate;
    $user = $candidate->user;
    $jobRequisition = $record->requisition;

    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($name) => mb_strtoupper(mb_substr($name, 0, 1)))
        ->implode('');

    //TODO: Replace with real data
    $skills = ['Laravel', 'Vue.js', 'PHP', 'JavaScript', 'MySQL', 'Docker'];
    $experience = '5+ years';
    $availability = 'Available immediately';
    $location = 'San Francisco, CA';
    $education = 'Computer Science, University Name';
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
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border">
                    <img
                        src="https://placehold.co/80x80/16a34a/ffffff?text={{ $initials }}"
                        alt="user profile image"
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
                        <x-filament::badge>
                            {{ $record->status->getLabel() }}
                        </x-filament::badge>
                        <x-filament::badge>
                            {{ $record->source->getLabel() }}
                        </x-filament::badge>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-x-6 gap-y-3 pt-1">
                {{-- Email --}}
                <div class="flex items-center gap-2">
                    <x-he4rt::icon icon="heroicon-o-envelope" size="sm" class="text-icon-medium" />
                    <span>{{ $user->email }}</span>
                </div>

                {{-- Phone --}}
                @if ($candidate->phone_number)
                    <div class="flex items-center gap-2">
                        <x-he4rt::icon icon="heroicon-o-phone" size="sm" class="text-icon-medium" />
                        <span>{{ $candidate->phone_number }}</span>
                    </div>
                @endif

                <div class="flex gap-2">
                    {{-- LinkedIn --}}
                    @if ($candidate->linkedin_url)
                        <x-filament::badge icon="heroicon-o-link" class="bg-black p-2">
                            <a href="{{ $candidate->linkedin_url }}" target="_blank" class="hover:text-blue-400">
                                LinkedIn
                            </a>
                        </x-filament::badge>
                    @endif

                    {{-- Portfolio --}}
                    @if ($candidate->portfolio_url)
                        <x-filament::badge icon="heroicon-o-globe-alt" class="bg-black p-2">
                            <a href="{{ $candidate->portfolio_url }}" target="_blank" class="hover:text-blue-400">
                                Portfolio
                            </a>
                        </x-filament::badge>
                    @endif
                </div>
            </div>
        </div>
        {{-- Application Info --}}
        <div class="border-border border-outline-low w-full">
            <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div>
            <span class="flex items-center gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-briefcase" size="sm" class="text-icon-medium" />
                Position
            </span>
                    <p class="text-foreground text-sm font-medium">
                        {{ $jobRequisition->post->title }}
                    </p>
                </div>

                <div>
            <span class="flex items-center gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-building-office-2" size="sm" class="text-icon-medium" />
                Department
            </span>
                    <p class="text-foreground text-sm font-medium">
                        {{ $jobRequisition->team->name }}
                    </p>
                </div>

                <div>
            <span class="flex items-center gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-calendar" size="sm" class="text-icon-medium" />
                Applied
            </span>
                    <p class="text-foreground text-sm font-medium">
                        {{ $record->created_at->format('M j, Y') }}
                    </p>
                </div>

                <div>
            <span class="flex gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-briefcase" size="sm" class="text-icon-medium" />
                Experience
            </span>
                    <p class="text-text-high text-sm font-semibold">{{ $experience }}</p>
                </div>

                <div>
            <span class="flex gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-clock" size="sm" class="text-icon-medium" />
                Availability
            </span>
                    <p class="text-text-high text-sm font-semibold">{{ $availability }}</p>
                </div>

                <div>
            <span class="flex gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-map-pin" size="sm" class="text-icon-medium" />
                Location
            </span>
                    <p class="text-text-high text-sm font-semibold">{{ $location }}</p>
                </div>

                <div>
            <span class="flex gap-1 mb-1 text-xs tracking-wider text-gray-500 uppercase">
                <x-he4rt::icon icon="heroicon-o-academic-cap" size="sm" class="text-icon-medium" />
                Education
            </span>
                    <p class="text-text-high text-sm font-semibold">{{ $education }}</p>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <x-he4rt::icon icon="heroicon-o-code-bracket" size="sm" class="text-icon-medium" />
                        <span class="text-text-medium text-xs font-semibold tracking-wider uppercase">
                    Key Skills
                </span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <x-filament::badge size="sm" color="gray">
                                {{ $skill }}
                            </x-filament::badge>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

    </header>
</x-filament::section>
