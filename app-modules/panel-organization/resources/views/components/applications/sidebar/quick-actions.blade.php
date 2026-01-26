@props([
    'record',
])

@php
    $candidate = $record->candidate;
    $user = $candidate->user;

    // Quick actions configuration
    $actions = [
        [
            'id' => 'advance',
            'label' => 'Advance Stage',
            'icon' => 'heroicon-o-arrow-right-circle',
            'color' => 'primary',
            'description' => 'Move candidate to next stage',
        ],
        [
            'id' => 'schedule',
            'label' => 'Schedule Interview',
            'icon' => 'heroicon-o-calendar-days',
            'color' => 'success',
            'description' => 'Schedule interview with candidate',
        ],
        [
            'id' => 'email',
            'label' => 'Send Email',
            'icon' => 'heroicon-o-envelope',
            'color' => 'info',
            'description' => 'Send email to candidate',
        ],
        [
            'id' => 'comment',
            'label' => 'Add Comment',
            'icon' => 'heroicon-o-chat-bubble-left-ellipsis',
            'color' => 'gray',
            'description' => 'Add internal comment',
        ],
        [
            'id' => 'reject',
            'label' => 'Reject',
            'icon' => 'heroicon-o-x-circle',
            'color' => 'danger',
            'description' => 'Reject candidate application',
        ],
        [
            'id' => 'more',
            'label' => 'More Actions',
            'icon' => 'heroicon-o-ellipsis-horizontal',
            'color' => 'gray',
            'description' => 'View more actions menu',
        ],
    ];
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-text-high text-sm font-semibold">Quick Actions</h3>
        <x-he4rt::icon icon="heroicon-o-bolt" size="sm" class="text-icon-medium" />
    </div>

    {{-- Primary Actions Grid --}}
    <div class="grid grid-cols-2 gap-3">
        {{-- Advance Stage (Primary) --}}
        <x-filament::button color="primary" size="md" class="justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-arrow-right-circle" size="sm" />
            <span class="ml-2">Advance</span>
        </x-filament::button>

        {{-- Schedule Interview --}}
        <x-filament::button color="success" size="md" class="justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-calendar-days" size="sm" />
            <span class="ml-2">Schedule</span>
        </x-filament::button>
    </div>

    {{-- Secondary Actions - Full Width --}}
    <div class="space-y-2">
        {{-- Email Action --}}
        <x-filament::button color="info" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-envelope" size="sm" />
            <span class="ml-2">Send Email to {{ $user->name }}</span>
        </x-filament::button>

        {{-- Add Comment --}}
        <x-filament::button color="gray" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-chat-bubble-left-ellipsis" size="sm" />
            <span class="ml-2">Add Internal Comment</span>
        </x-filament::button>
    </div>

    {{-- Divider --}}
    <div class="border-outline-low border-t"></div>

    {{-- Danger Zone --}}
    <div class="space-y-2">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">Danger Zone</h4>

        <x-filament::button color="danger" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-x-circle" size="sm" />
            <span class="ml-2">Reject Application</span>
        </x-filament::button>
    </div>

    {{-- More Actions Dropdown Trigger --}}
    <div class="border-outline-low border-t pt-3">
        <x-filament::button color="gray" size="sm" class="w-full justify-center" disabled>
            <x-he4rt::icon icon="heroicon-o-ellipsis-horizontal" size="sm" />
            <span class="ml-2">More Actions</span>
        </x-filament::button>
    </div>

    {{-- Action Status Info --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
        <div class="flex items-start gap-2">
            <x-he4rt::icon icon="heroicon-o-information-circle" size="sm" class="text-primary mt-0.5 shrink-0" />
            <div class="text-text-medium space-y-1 text-xs">
                <p>
                    <span class="text-primary font-semibold">Status:</span>
                    Actions are in visual-only mode
                </p>
                <p>
                    <span class="font-semibold">Current Stage:</span>
                    {{ $record->current_stage?->name ?? 'Not set' }}
                </p>
                <p>
                    <span class="font-semibold">Last Updated:</span>
                    {{ $record->updated_at->diffForHumans() }}
                </p>
            </div>
        </div>
    </div>
</div>
