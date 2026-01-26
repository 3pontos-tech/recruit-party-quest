<div class="screening-question">
    <div class="mb-2 flex items-center justify-between">
        <x-he4rt::heading size="2xs">
            <x-screening::questions.question-base :$question />
        </x-he4rt::heading>
    </div>

    {{ $this->form }}
</div>
