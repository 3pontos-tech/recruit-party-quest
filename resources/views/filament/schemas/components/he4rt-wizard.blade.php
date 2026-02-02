@php
    $isContained = $isContained();
    $key = $getKey();
    $previousAction = $getAction("previous");
    $nextAction = $getAction("next");
    $steps = $getChildSchema()->getComponents();
    $isHeaderHidden = $isHeaderHidden();
@endphp

<div
    x-load
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc("wizard", "filament/schemas") }}"
    x-data="wizardSchemaComponent({
                isSkippable: @js($isSkippable()),
                isStepPersistedInQueryString: @js($isStepPersistedInQueryString()),
                key: @js($key),
                startStep: @js($getStartStep()),
                stepQueryStringKey: @js($getStepQueryStringKey()),
            })"
    x-on:next-wizard-step.window="if ($event.detail.key === @js($key)) goToNextStep()"
    x-on:go-to-wizard-step.window="$event.detail.key === @js($key) && goToStep($event.detail.step)"
    wire:ignore.self
    {{
        $attributes
            ->merge(
                [
                    "id" => $getId(),
                ],
                escape: false,
            )
            ->merge($getExtraAttributes(), escape: false)
            ->merge($getExtraAlpineAttributes(), escape: false)
            ->class([
                "fi-sc-wizard fi-hp-wizard",
                "fi-sc-wizard-header-hidden" => $isHeaderHidden,
            ])
    }}
>
    <input
        type="hidden"
        value="{{
            collect($steps)
                ->filter(static fn (\Filament\Schemas\Components\Wizard\Step $step): bool => $step->isVisible())
                ->map(static fn (\Filament\Schemas\Components\Wizard\Step $step): ?string => $step->getKey())
                ->values()
                ->toJson()
        }}"
        x-ref="stepsData"
    />

    @if (! $isHeaderHidden)
        <ol
            @if (filled($label = $getLabel()))
                aria-label="{{ $label }}"
            @endif
            role="list"
            x-cloak
            x-ref="header"
            class="fi-sc-wizard-header fi-hp-wizard-header top-8! lg:sticky!"
        >
            @foreach ($steps as $step)
                <li
                    class="fi-sc-wizard-header-step fi-hp-wizard-header-step"
                    x-bind:class="{
                        'fi-active': getStepIndex(step) === {{ $loop->index }},
                        'fi-completed': getStepIndex(step) > {{ $loop->index }},
                    }"
                >
                    <button
                        type="button"
                        x-bind:aria-current="getStepIndex(step) === {{ $loop->index }} ? 'step' : null"
                        x-on:click="step = @js($step->getKey())"
                        x-bind:disabled="! isStepAccessible(@js($step->getKey())) || @js($previousAction->isDisabled())"
                        role="step"
                        class="fi-sc-wizard-header-step-btn fi-hp-wizard-header-step-btn"
                    >
                        <div class="fi-sc-wizard-header-step-icon-ctn fi-hp-wizard-header-step-icon-ctn">
                            @php
                                $completedIcon = $step->getCompletedIcon();
                            @endphp

                            {{
                                \Filament\Support\generate_icon_html(
                                    $completedIcon ?? \Filament\Support\Icons\Heroicon::OutlinedCheck,
                                    alias: filled($completedIcon) ? null : \Filament\Schemas\View\SchemaIconAlias::COMPONENTS_WIZARD_COMPLETED_STEP,
                                    attributes: new \Illuminate\View\ComponentAttributeBag([
                                        "x-cloak" => "x-cloak",
                                        "x-show" => "getStepIndex(step) > {$loop->index}",
                                    ]),
                                    size: \Filament\Support\Enums\IconSize::ExtraSmall,
                                )
                            }}

                            @if (filled($icon = $step->getIcon()))
                                {{
                                    \Filament\Support\generate_icon_html(
                                        $icon,
                                        attributes: new \Illuminate\View\ComponentAttributeBag([
                                            "x-cloak" => "x-cloak",
                                            "x-show" => "getStepIndex(step) <= {$loop->index}",
                                        ]),
                                        size: \Filament\Support\Enums\IconSize::ExtraSmall,
                                    )
                                }}
                            @else
                                <span
                                    x-show="getStepIndex(step) <= {{ $loop->index }}"
                                    class="fi-sc-wizard-header-step-number fi-hp-wizard-header-step-number"
                                >
                                    {{ $loop->index + 1 }}
                                </span>
                            @endif
                        </div>

                        <div class="fi-sc-wizard-header-step-text fi-hp-wizard-header-step-text">
                            @if (! $step->isLabelHidden())
                                <div class="flex w-full items-center justify-between gap-2">
                                    <span class="fi-sc-wizard-header-step-label fi-hp-wizard-header-step-label">
                                        {{ $step->getLabel() }}
                                    </span>
                                    <span
                                        x-cloak
                                        x-show="getStepIndex(step) > {{ $loop->index }}"
                                        class="fi-hp-wizard-header-step-status-completed"
                                    >
                                        Finished
                                    </span>
                                    <span
                                        x-cloak
                                        x-show="getStepIndex(step) === {{ $loop->index }}"
                                        class="fi-hp-wizard-header-step-status-waiting"
                                    >
                                        Waiting
                                    </span>
                                </div>
                            @endif

                            @if (filled($description = $step->getDescription()))
                                <span class="fi-sc-wizard-header-step-description fi-hp-wizard-header-step-description">
                                    {{ $description }}
                                </span>
                            @endif
                        </div>
                    </button>

                    @if (! $loop->last)
                    @endif
                </li>
            @endforeach
        </ol>
    @endif

    <div class="order-2 flex h-full flex-col lg:order-1">
        @foreach ($steps as $step)
            {{ $step }}
        @endforeach

        <div x-cloak class="fi-sc-wizard-footer md:mt-auto! md:pt-8">
            <div
                x-cloak
                @if (! $previousAction->isDisabled())
                    x-on:click="goToPreviousStep"
                @endif
                x-show="! isFirstStep()"
            >
                {{ $previousAction }}
            </div>

            <div x-show="isFirstStep()">
                {{ $getCancelAction() }}
            </div>

            <div
                x-cloak
                @if (! $nextAction->isDisabled())
                    x-on:click="requestNextStep()"
                @endif
                x-bind:class="{ 'fi-hidden': isLastStep() }"
                wire:loading.class="fi-disabled"
            >
                {{ $nextAction }}
            </div>

            <div x-bind:class="{ 'fi-hidden': ! isLastStep() }">
                {{ $getSubmitAction() }}
            </div>
        </div>
    </div>
</div>
