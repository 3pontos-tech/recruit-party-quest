@php
    use Filament\Support\Icons\Heroicon;

    $currentRequisition = $this->requisition;
@endphp

<div class="flex flex-wrap items-center gap-2">
    <button
        type="button"
        wire:click="closeRequisition"
        class="text-text-medium hover:text-text-high inline-flex items-center gap-1 text-sm font-medium"
    >
        <x-he4rt::icon :icon="Heroicon::ArrowLeft" size="sm" />
        {{ __('panel-organization::workspace.switcher.back') }}
    </button>
    <span class="text-text-low">/</span>
    <div
        x-data="{ open: false }"
        x-on:click.outside="open = false"
        x-on:keydown.escape.window="open = false"
        class="relative min-w-0 grow sm:max-w-md"
    >
        <label class="relative block">
            <span class="sr-only">{{ __('panel-organization::workspace.switcher.label') }}</span>
            <x-he4rt::icon
                :icon="Heroicon::Briefcase"
                size="sm"
                class="text-icon-medium pointer-events-none absolute top-1/2 left-3 -translate-y-1/2"
            />
            <input
                type="search"
                wire:model.live.debounce.300ms="requisitionSearch"
                x-on:focus="open = true"
                x-on:input="open = true"
                placeholder="{{ $currentRequisition?->post?->title ?? __('panel-organization::workspace.switcher.placeholder') }}"
                autocomplete="off"
                class="bg-elevation-01dp border-outline-low/50 text-text-high placeholder:text-text-high focus:border-primary focus:ring-primary/30 w-full rounded-lg border py-2 pr-9 pl-9 text-sm font-medium focus:ring-2 focus:outline-none"
            />
            <x-he4rt::icon
                :icon="Heroicon::ChevronDown"
                size="sm"
                class="text-icon-medium pointer-events-none absolute top-1/2 right-3 -translate-y-1/2"
            />
        </label>
        <ul
            x-show="open"
            x-cloak
            x-transition.opacity
            class="border-outline-low/40 bg-elevation-01dp absolute z-20 mt-1 max-h-80 w-full overflow-auto rounded-lg border p-1 shadow-xl"
        >
            @forelse ($this->requisitionResults as $result)
                <li wire:key="switch-{{ $result->getKey() }}">
                    <button
                        type="button"
                        wire:click="openRequisition('{{ $result->getKey() }}')"
                        x-on:click="open = false"
                        class="{{ $result->getKey() === $currentRequisition?->getKey() ? 'bg-elevation-02dp text-text-high' : 'text-text-medium hover:bg-elevation-02dp/60 hover:text-text-high' }} flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-left text-sm"
                    >
                        <span class="truncate">
                            {{ $result->post?->title ?? __('panel-organization::workspace.overview.untitled') }}
                        </span>
                        <span class="text-text-low shrink-0 font-mono text-xs tabular-nums">
                            {{ trans_choice('panel-organization::workspace.switcher.new_count', $result->new_applications_count, ['count' => $result->new_applications_count]) }}
                        </span>
                    </button>
                </li>
            @empty
                <li class="text-text-low px-3 py-2 text-xs">
                    {{ __('panel-organization::workspace.switcher.empty') }}
                </li>
            @endforelse
        </ul>
    </div>
</div>
