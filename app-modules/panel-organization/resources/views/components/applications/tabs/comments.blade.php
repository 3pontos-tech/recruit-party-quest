@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    $comments = $record->comments;
@endphp

{{-- Main Header --}}
<div class="flex items-center gap-3">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-700">
        <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::ChatBubbleBottomCenterText" size="sm" />
    </div>
    <div>
        <h3 class="text-text-high text-lg font-semibold">{{ __('panel-organization::view.tabs.comments.title') }}</h3>
        <p class="text-text-medium text-sm">{{ __('panel-organization::view.tabs.comments.subtitle') }}</p>
    </div>
</div>

@forelse ($comments as $comment)
    <div class="bg-surface-01dp border-outline-low mt-6 space-y-4 rounded-lg border p-4">
        <div class="space-y-4">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-pink-50 text-pink-600">
                    <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::ChatBubbleLeftEllipsis" size="sm" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">{{ $comment->author->name }}</h3>
                    <p class="text-text-medium text-sm">
                        {{ __('panel-organization::view.tabs.comments.published_at') . $comment->created_at }}
                    </p>
                </div>
            </div>

            {{-- Cover Letter Content --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg p-2">
                <div class="prose prose-sm max-w-none">
                    <div class="text-text-high leading-relaxed">
                        {{ $comment->content }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <h3 class="text-text-high mt-6 text-lg font-semibold">There are no comments yet.</h3>
@endforelse
