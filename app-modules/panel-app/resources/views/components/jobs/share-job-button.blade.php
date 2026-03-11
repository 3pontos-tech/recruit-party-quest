@php
    use Filament\Support\Icons\Heroicon;
    use Illuminate\Support\Js;
@endphp

@props([
    'job',
    'variant' => 'icon-only',
    //'icon-only'|'icon-text''size' => 'sm',
])

@php
    /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */
    $jobUrl = He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job]);
    $jobTitle = $job->post?->title ?? 'Vaga';
@endphp

<div
    x-data="{ copied: false }"
    x-on:link-copied.stop="
        copied = true
        setTimeout(() => (copied = false), 2000)
    "
    class="relative w-full"
>
    @if ($variant === 'icon-only')
        {{-- Icon Only Variant --}}
        <x-he4rt::button
            variant="outline"
            :size="$size"
            class="size-8"
            :icon="Heroicon::Share"
            x-on:click.stop.prevent="async () => {
                const url = {{ Js::from($jobUrl) }};
                const title = {{ Js::from($jobTitle) }};
                if (navigator.share) {
                    await navigator.share({ title, url });
                } else {
                    await navigator.clipboard.writeText(url);
                    $dispatch('link-copied');
                }
            }"
            aria-label="{{ __('panel-app::filament.components.share_button.share_job') }}"
        />
    @else
        {{-- Icon + Text Variant --}}
        <button
            type="button"
            x-on:click.stop.prevent="
                async () => {
                    const url = {{ Js::from($jobUrl) }}
                    const title = {{ Js::from($jobTitle) }}
                    if (navigator.share) {
                        await navigator.share({ title, url })
                    } else {
                        await navigator.clipboard.writeText(url)
                        copied = true
                        setTimeout(() => (copied = false), 2000)
                    }
                }
            "
            class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 flex w-full items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition duration-200"
            aria-label="{{ __('panel-app::filament.components.share_button.share_job') }}"
        >
            <x-he4rt::icon icon="heroicon-o-share" class="size-4" />
            <span x-show="!copied">{{ __('panel-app::filament.components.share_button.share') }}</span>
            <span x-show="copied" x-cloak>{{ __('panel-app::filament.components.share_button.copied') }}</span>
        </button>
    @endif

    {{-- Tooltip (only for icon-only variant) --}}
    @if ($variant === 'icon-only')
        <span
            x-show="copied"
            x-cloak
            x-transition.opacity
            class="bg-surface-primary dark:bg-surface-primary-dark border-outline-light dark:border-outline-dark text-text-high pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 rounded-md border px-2 py-1 text-xs whitespace-nowrap shadow"
        >
            {{ __('panel-app::filament.components.share_button.copied') }}
        </span>
    @endif
</div>
