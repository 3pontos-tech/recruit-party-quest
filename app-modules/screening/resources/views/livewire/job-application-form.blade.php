@php
    use He4rt\Screening\Enums\QuestionTypeEnum;
    use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
@endphp

<form wire:submit="submit" class="space-y-6">
    @foreach ($requisition->screeningQuestions as $question)
        @php
            $component = QuestionTypeRegistry::get($question->question_type)::component();
        @endphp

        <x-dynamic-component
            :component="$component"
            :question="$question"
            wire:model="responses.{{ $question->id }}"
        />
    @endforeach

    <div class="flex justify-end pt-4">
        <x-he4rt::button type="submit" variant="solid">Submit Application</x-he4rt::button>
    </div>
</form>
