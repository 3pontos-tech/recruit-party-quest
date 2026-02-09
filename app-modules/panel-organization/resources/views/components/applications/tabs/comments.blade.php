@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $comments = $record->comments;
    $hasComments = $comments->isNotEmpty();
@endphp

<x-filament::section icon="heroicon-o-chat-bubble-bottom-center-text" icon-color="warning">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.comments.title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.comments.subtitle') }}
    </x-slot>

    @if ($hasComments)
        @foreach ($comments as $comment)
            <x-filament::section
                :icon="\Filament\Support\Icons\Heroicon::ChatBubbleLeftEllipsis"
                icon-color="warning"
                class="mb-5"
            >
                <x-slot name="heading">
                    {{ $comment->author->name }}
                </x-slot>
                <x-slot name="afterHeader">
                    {{ __('panel-organization::view.tabs.comments.published_at') . $comment->created_at }}
                </x-slot>
                {{-- Comment Content --}}
                <div class="prose prose-sm max-w-none">
                    <div class="text-text-high text-base leading-7">
                        {{ $comment->content }}
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    @else
        <x-filament::section :secondary="true">
            <div class="text-center">
                <h3 class="text-text-high text-lg font-semibold">There are no comments yet.</h3>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
