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

<x-filament::section heading="Quick Actions" icon="heroicon-o-bolt">
    <div class="flex flex-col gap-3">
        @foreach ($this->getActions() as $action)
            {{ $action }}
        @endforeach

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

        {{-- Email Action --}}
        <x-filament::button color="info" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-envelope" size="sm" />
            <span class="ml-2">Send Email</span>
        </x-filament::button>

        {{-- Add Comment --}}
        <x-filament::button color="gray" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-chat-bubble-left-ellipsis" size="sm" />
            <span class="ml-2">Add Internal Comment</span>
        </x-filament::button>

        <x-filament::button color="danger" size="sm" outlined class="w-full justify-start" disabled>
            <x-he4rt::icon icon="heroicon-o-x-circle" size="sm" />
            <span class="ml-2">Reject Application</span>
        </x-filament::button>
    </div>
    <x-slot:footer>
        <div class="text-text-medium space-y-1 text-xs">
            <p>
                <span class="font-semibold">Current Stage:</span>
                {{ $record->currentStage()?->name ?? 'Not set' }}
            </p>
            <p>
                <span class="font-semibold">Last Updated:</span>
                {{ $record->updated_at->diffForHumans() }}
            </p>
        </div>
    </x-slot>
</x-filament::section>
