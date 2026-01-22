<div class="screening-question">
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            {{ $question->question_text }}
            @if ($question->is_required)
                <span class="text-helper-error">*</span>
            @endif
        </x-he4rt::heading>
    </div>

    {{ $this->form }}
</div>
