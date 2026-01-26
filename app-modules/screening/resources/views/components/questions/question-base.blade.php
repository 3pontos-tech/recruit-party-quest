@props([
    'question',
    'message' => '',
])

{{ $question->question_text }}
@if ($question->is_required)
    <span class="text-helper-error">*</span>
@endif

@error("responses.{$question->id}")
    <p class="mt-1 text-sm text-red-500">
        {{ $message }}
    </p>
@enderror
