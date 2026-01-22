@php
    use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
@endphp

<form
    wire:submit="submit"
    class="space-y-8"
    x-data="{
        requiredIds: @js($requiredQuestionIds),
        get isValid() {
            return this.requiredIds.every((id) => {
                const value = $wire.responses[id]
                if (Array.isArray(value)) {
                    return value.length > 0
                }
                return value !== null && value !== '' && value !== undefined
            })
        },
    }"
>
    @foreach ($requisition->screeningQuestions as $question)
        @php
            $component = QuestionTypeRegistry::get($question->question_type)::component();
        @endphp

        <x-dynamic-component
            :component="$component"
            :question="$question"
            wire:model.defer="responses.{{ $question->id }}"
        />
    @endforeach

    <div class="flex justify-end pt-4">
        <div class="relative" x-data="{ showTooltip: false }">
            <div @mouseenter="if (!isValid) showTooltip = true" @mouseleave="showTooltip = false">
                <x-he4rt::button type="submit" variant="solid" x-bind:disabled="!isValid">
                    Submit Application
                </x-he4rt::button>
            </div>

            <div
                x-show="showTooltip"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="translate-y-1 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-1 opacity-0"
                class="bg-elevation-05dp text-text-high absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 rounded-md px-3 py-2 text-sm whitespace-nowrap shadow-lg"
            >
                Please answer all required questions
            </div>
        </div>
    </div>
</form>
