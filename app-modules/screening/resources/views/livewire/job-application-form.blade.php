@php
    use He4rt\Screening\Enums\QuestionTypeEnum;
@endphp

<form wire:submit="submit">
    <h2>{{ $requisition->title }}</h2>

    @foreach ($requisition->screeningQuestions as $question)
        @php
            $component = match ($question->question_type) {
                QuestionTypeEnum::YesNo => 'screening::questions.yes-no',
                QuestionTypeEnum::Text => 'screening::questions.text',
                QuestionTypeEnum::Number => 'screening::questions.number',
                QuestionTypeEnum::SingleChoice => 'screening::questions.single-choice',
                QuestionTypeEnum::MultipleChoice => 'screening::questions.multiple-choice',
            };
        @endphp

        <x-dynamic-component
            :component="$component"
            :question="$question"
            wire:model="responses.{{ $question->id }}"
        />
    @endforeach

    <button type="submit">Apply</button>
</form>
