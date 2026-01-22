@props([
    'question',
    'disabled' => false,
])

<livewire:screening-file-upload-question :question="$question" :disabled="$disabled" />
