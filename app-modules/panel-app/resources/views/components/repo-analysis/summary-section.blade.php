@props([
    'summary',
])

@if ($summary)
    <x-he4rt::card>
        <x-slot:title>
            {{ __('repo-analysis::labels.components.summary_section.heading') }}
        </x-slot>
        <p class="pt-2 leading-relaxed text-gray-700 dark:text-gray-300">
            {{ $summary }}
        </p>
    </x-he4rt::card>
@endif
