@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Js;
@endphp

@props([
    'job',
    'size' => 'sm',
])

@if (! $job->is_internal_only)
    @php
        /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */
        $jobUrl = $job->post ? He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job->post->slug]) : '#';
        $jobTitle = $job->post?->title ?? 'Vaga';
    @endphp

    <div
        x-data="{ copied: false }"
        x-on:link-copied.stop="
            copied = true
            setTimeout(() => (copied = false), 2000)
        "
        class="relative"
    >
        <x-he4rt::button
            variant="outline"
            icon="heroicon-o-clipboard-document-check"
            :size="$size"
            class="flex-1 px-4 py-2"
            x-on:click.stop.prevent="
                    const url = {{ Js::from($jobUrl) }};
                    const title = {{ Js::from($jobTitle) }};
                    if (navigator.share) {
                        await navigator.share({ title, url });
                    } else {
                        await navigator.clipboard.writeText(url);
                        $dispatch('link-copied');
                    }
                "
            aria-label="{{ __('panel-app::filament.components.share_button.share_job') }}"
        />

        {{-- Tooltip (only for icon-only variant) --}}
        <span
            x-show="copied"
            x-cloak
            x-transition.opacity
            class="bg-surface-primary dark:bg-surface-primary-dark border-outline-light dark:border-outline-dark text-text-high pointer-events-none absolute -bottom-8 left-1/2 -translate-x-1/2 rounded-md border px-2 py-1 text-xs whitespace-nowrap shadow"
        >
            {{ __('panel-app::filament.components.share_button.copied') }}
        </span>
    </div>
@endif
