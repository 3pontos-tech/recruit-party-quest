@php
    use Filament\Support\Icons\Heroicon;
    use He4rt\Applications\Models\Application;
    use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
    use He4rt\Organization\Filament\Resources\Recruitment\Applications\Support\ApplicationNavigationContext;

    /** @var ApplicationNavigationContext $context */
    /** @var Application $record */
@endphp

<div
    class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white px-4 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
    role="navigation"
    aria-label="{{ __('panel-organization::view.navigation.aria_label') }}"
>
    {{-- Previous --}}
    @if ($context->previous)
        <a
            href="{{ ApplicationResource::getUrl('view', ['record' => $context->previous]) }}"
            class="hover:text-primary-600 inline-flex items-center gap-2 text-sm font-medium text-gray-900 transition dark:text-gray-100"
        >
            <x-he4rt::icon :icon="Heroicon::ArrowLeft" size="xs" />
            <span>{{ __('panel-organization::view.navigation.previous') }}</span>
            <span
                class="hidden border-l border-gray-200 pl-2 text-xs font-normal text-gray-500 md:inline dark:border-gray-700"
            >
                {{ $context->previous->candidate?->user?->name }}
            </span>
        </a>
    @else
        <button
            type="button"
            disabled
            class="inline-flex cursor-not-allowed items-center gap-2 text-sm font-medium text-gray-400 dark:text-gray-600"
        >
            <x-he4rt::icon :icon="Heroicon::ArrowLeft" size="xs" />
            <span>{{ __('panel-organization::view.navigation.previous') }}</span>
        </button>
    @endif

    {{-- Counter + Dropdown --}}
    <div class="flex items-center gap-3">
        <span class="text-xs tracking-wider text-gray-500 uppercase tabular-nums dark:text-gray-400">
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $context->position }}</span>
            {{ __('panel-organization::view.navigation.of') }}
            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $context->total }}</span>
        </span>

        <div x-data="{ open: false }" class="relative hidden md:block">
            <button
                type="button"
                x-on:click="open = !open"
                x-on:click.outside="open = false"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-sm font-medium text-gray-900 transition hover:border-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <span class="bg-primary-500 h-1.5 w-1.5 rounded-full"></span>
                <span>{{ $record->candidate?->user?->name }}</span>
                <x-he4rt::icon :icon="Heroicon::ChevronDown" size="xs" class="text-gray-500" />
            </button>

            <ul
                x-show="open"
                x-cloak
                x-transition.opacity
                class="absolute right-0 z-20 mt-2 max-h-96 w-96 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                @foreach ($context->allActive as $candidatura)
                    @php
                        $isCurrent = $candidatura->id === $record->id;
                        $stageName = $candidatura->currentStage?->name;
                    @endphp

                    <li>
                        <a
                            href="{{ ApplicationResource::getUrl('view', ['record' => $candidatura]) }}"
                            @class([
                                'flex items-center justify-between gap-3 px-4 py-2.5 text-sm transition',
                                'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' => $isCurrent,
                                'text-gray-900 hover:bg-gray-50 dark:text-gray-100 dark:hover:bg-gray-800' => ! $isCurrent,
                            ])
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                @if ($isCurrent)
                                    <x-he4rt::icon :icon="Heroicon::Play" size="xs" class="shrink-0" />
                                @endif

                                <span class="truncate font-medium">{{ $candidatura->candidate?->user?->name }}</span>
                            </span>
                            @if ($stageName)
                                <span
                                    @class([
                                        'shrink-0 rounded-full border px-2 py-0.5 text-xs whitespace-nowrap',
                                        'border-white/25 bg-white/10' => $isCurrent,
                                        'border-gray-200 bg-gray-50 text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400' => ! $isCurrent,
                                    ])
                                >
                                    {{ $stageName }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Next --}}
    @if ($context->next)
        <a
            href="{{ ApplicationResource::getUrl('view', ['record' => $context->next]) }}"
            class="hover:text-primary-600 inline-flex items-center gap-2 text-sm font-medium text-gray-900 transition dark:text-gray-100"
        >
            <span
                class="hidden border-r border-gray-200 pr-2 text-xs font-normal text-gray-500 md:inline dark:border-gray-700"
            >
                {{ $context->next->candidate?->user?->name }}
            </span>
            <span>{{ __('panel-organization::view.navigation.next') }}</span>
            <x-he4rt::icon :icon="Heroicon::ArrowRight" size="xs" />
        </a>
    @else
        <button
            type="button"
            disabled
            class="inline-flex cursor-not-allowed items-center gap-2 text-sm font-medium text-gray-400 dark:text-gray-600"
        >
            <span>{{ __('panel-organization::view.navigation.next') }}</span>
            <x-he4rt::icon :icon="Heroicon::ArrowRight" size="xs" />
        </button>
    @endif
</div>
