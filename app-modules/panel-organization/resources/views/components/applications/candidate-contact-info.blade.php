@props([
    'record' => [],
])

@php
    $candidate = $record->candidate;
    $user = $candidate->user;
@endphp

<div class="space-y-4">
    {{-- Contact Information --}}
    <div class="space-y-3">
        <h3 class="text-text-high text-sm font-semibold tracking-wider uppercase">Contact Information</h3>

        <div class="space-y-3">
            {{-- Email --}}
            <div class="flex items-center gap-3">
                <div
                    class="bg-elevation-01dp border-outline-low flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                >
                    <x-he4rt::icon icon="heroicon-o-envelope" size="sm" class="text-icon-medium" />
                </div>
                <div class="flex-1">
                    <p class="text-text-high text-sm font-medium">{{ $user->email }}</p>
                    <p class="text-text-medium text-xs">Email</p>
                </div>
                <x-filament::button
                    size="xs"
                    outlined
                    icon="heroicon-o-paper-airplane"
                    tag="a"
                    href="mailto:{{ $user->email }}"
                >
                    Send
                </x-filament::button>
            </div>

            {{-- Phone --}}
            @if ($candidate->phone_number)
                <div class="flex items-center gap-3">
                    <div
                        class="bg-elevation-01dp border-outline-low flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                    >
                        <x-he4rt::icon icon="heroicon-o-phone" size="sm" class="text-icon-medium" />
                    </div>
                    <div class="flex-1">
                        <p class="text-text-high text-sm font-medium">{{ $candidate->phone_number }}</p>
                        <p class="text-text-medium text-xs">Phone</p>
                    </div>
                    <x-filament::button
                        size="xs"
                        outlined
                        icon="heroicon-o-phone"
                        tag="a"
                        href="tel:{{ $candidate->phone_number }}"
                    >
                        Call
                    </x-filament::button>
                </div>
            @endif
        </div>
    </div>

    {{-- Social Links --}}
    @if ($candidate->linkedin_url || $candidate->portfolio_url)
        <div class="border-outline-low border-t pt-4">
            <h3 class="text-text-high mb-3 text-sm font-semibold tracking-wider uppercase">Social Links</h3>

            <div class="space-y-3">
                {{-- LinkedIn --}}
                @if ($candidate->linkedin_url)
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-elevation-01dp border-outline-low flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                        >
                            <x-he4rt::icon icon="heroicon-o-link" size="sm" class="text-icon-medium" />
                        </div>
                        <div class="flex-1">
                            <p class="text-text-high text-sm font-medium">LinkedIn Profile</p>
                            <p class="text-text-medium truncate text-xs">{{ $candidate->linkedin_url }}</p>
                        </div>
                        <x-filament::button
                            size="xs"
                            outlined
                            icon="heroicon-o-arrow-top-right-on-square"
                            tag="a"
                            href="{{ $candidate->linkedin_url }}"
                            target="_blank"
                        >
                            View
                        </x-filament::button>
                    </div>
                @endif

                {{-- Portfolio --}}
                @if ($candidate->portfolio_url)
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-elevation-01dp border-outline-low flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                        >
                            <x-he4rt::icon icon="heroicon-o-globe-alt" size="sm" class="text-icon-medium" />
                        </div>
                        <div class="flex-1">
                            <p class="text-text-high text-sm font-medium">Portfolio</p>
                            <p class="text-text-medium truncate text-xs">{{ $candidate->portfolio_url }}</p>
                        </div>
                        <x-filament::button
                            size="xs"
                            outlined
                            icon="heroicon-o-arrow-top-right-on-square"
                            tag="a"
                            href="{{ $candidate->portfolio_url }}"
                            target="_blank"
                        >
                            View
                        </x-filament::button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Additional Info --}}
    <div class="border-outline-low border-t pt-4">
        <h3 class="text-text-high mb-3 text-sm font-semibold tracking-wider uppercase">Application Details</h3>

        <div class="space-y-2">
            {{-- Source --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Source</span>
                <span class="text-text-high text-xs font-medium">Direct Application</span>
            </div>

            {{-- Referral --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Referral</span>
                <span class="text-text-high text-xs font-medium">None</span>
            </div>

            {{-- Time Zone --}}
            <div class="flex items-center justify-between">
                <span class="text-text-medium text-xs">Time Zone</span>
                <span class="text-text-high text-xs font-medium">{{ $record->created_at->format('T') }}</span>
            </div>
        </div>
    </div>
</div>
