<div class="screening-question">
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            {{ $question->question_text }}
            @if ($question->is_required)
                <span class="text-helper-error">*</span>
            @endif
        </x-he4rt::heading>

        @if ($question->is_knockout)
            <x-he4rt::text class="text-helper-warning font-family-secondary shrink-0 self-start text-sm">
                (pergunta eliminatória)
            </x-he4rt::text>
        @endif
    </div>

    {{ $this->form }}
</div>
