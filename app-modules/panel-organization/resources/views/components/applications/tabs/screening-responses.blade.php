@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    use He4rt\Screening\Presenters\ScreeningResponsePresenter;

    $responses = $record
        ->screeningResponses()
        ->with('question')
        ->get()
        ->sortBy('question.display_order');

    $hasResponses = $responses->isNotEmpty();
    $knockoutCount = $responses->where('is_knockout_fail', true)->count();
@endphp

<x-filament::section icon="heroicon-o-clipboard-document-check" icon-color="success">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.screening_responses.title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.screening_responses.subtitle') }}
    </x-slot>

    @if ($hasResponses)
        <x-slot name="afterHeader">
            <div class="flex items-center gap-2">
                <x-he4rt::tag size="sm">
                    {{ trans_choice('panel-organization::view.tabs.screening_responses.response_count', $responses->count(), ['count' => $responses->count()]) }}
                </x-he4rt::tag>
                @if ($knockoutCount > 0)
                    <x-he4rt::tag size="sm" color="danger">
                        {{ trans_choice('panel-organization::view.tabs.screening_responses.knockout_count', $knockoutCount, ['count' => $knockoutCount]) }}
                    </x-he4rt::tag>
                @endif
            </div>
        </x-slot>
    @endif

    @if ($hasResponses)
        <div class="space-y-4">
            @foreach ($responses as $response)
                @php
                    $presenter = new ScreeningResponsePresenter($response);
                @endphp

                <div
                    @class([
                        'bg-elevation-02dp border-outline-low rounded-lg border p-4',
                        'ring-danger-500/20 border-danger-500/30 ring-2' => $response->is_knockout_fail,
                    ])
                >
                    {{-- Question Header --}}
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div
                                @class([
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                    'bg-danger-500/10' => $response->is_knockout_fail,
                                    'bg-primary-500/10' => ! $response->is_knockout_fail,
                                ])
                            >
                                <x-he4rt::icon
                                    :icon="$presenter->icon()"
                                    size="sm"
                                    @class([
                                        'text-danger-500' => $response->is_knockout_fail,
                                        'text-primary-500' => ! $response->is_knockout_fail,
                                    ])
                                />
                            </div>
                            <div>
                                <h4 class="text-text-high text-sm font-medium">
                                    {{ $response->question->question_text }}
                                </h4>
                                <span class="text-text-low text-xs">
                                    {{ $presenter->typeLabel() }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($response->question->is_required)
                                <x-he4rt::tag size="xs">
                                    {{ __('panel-organization::view.tabs.screening_responses.required') }}
                                </x-he4rt::tag>
                            @endif

                            @if ($response->question->is_knockout)
                                <x-he4rt::tag size="xs" color="warning">
                                    {{ __('panel-organization::view.tabs.screening_responses.knockout') }}
                                </x-he4rt::tag>
                            @endif
                        </div>
                    </div>

                    {{-- Response Value --}}
                    <div
                        @class([
                            'rounded-lg p-3',
                            'bg-danger-500/5' => $response->is_knockout_fail,
                            'bg-elevation-01dp' => ! $response->is_knockout_fail,
                        ])
                    >
                        @if ($presenter->isList())
                            {{-- Multiple Choice: render as checkbox-like list --}}
                            <div class="space-y-2">
                                @foreach ($presenter->listItems() as $item)
                                    <div class="flex items-center gap-2">
                                        @if ($item['selected'])
                                            <x-he4rt::icon
                                                icon="heroicon-s-check-circle"
                                                size="sm"
                                                class="text-success-500 shrink-0"
                                            />
                                        @else
                                            <div class="border-outline-low h-5 w-5 shrink-0 rounded-full border"></div>
                                        @endif
                                        <span
                                            @class([
                                                'text-sm',
                                                'text-text-high font-medium' => $item['selected'],
                                                'text-text-low' => ! $item['selected'],
                                            ])
                                        >
                                            {{ $item['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Scalar value --}}
                            <p class="text-text-high text-sm leading-relaxed">
                                {{ $presenter->displayValue() }}
                            </p>
                        @endif
                    </div>

                    {{-- Knockout Fail Warning --}}
                    @if ($response->is_knockout_fail)
                        <div class="mt-3 flex items-center gap-2">
                            <x-he4rt::icon icon="heroicon-s-exclamation-triangle" size="sm" class="text-danger-500" />
                            <span class="text-danger-500 text-xs font-medium">
                                {{ __('panel-organization::view.tabs.screening_responses.knockout_fail') }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <x-filament::section :secondary="true">
            <div class="py-4 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::ClipboardDocumentCheck"
                    size="lg"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.screening_responses.no_responses') }}
                </h4>
                <p class="text-text-medium mt-1 text-sm">
                    {{ __('panel-organization::view.tabs.screening_responses.no_responses_text') }}
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
