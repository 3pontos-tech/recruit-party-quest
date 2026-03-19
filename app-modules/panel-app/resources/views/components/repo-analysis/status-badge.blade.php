@props([
    'status',
])

@php
    use He4rt\RepoAnalysis\Enums\AnalysisStatus;

    $classes = match ($status) {
        AnalysisStatus::Pending => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        AnalysisStatus::Analyzing => 'bg-info-100 text-info-600 dark:bg-info-900/30 dark:text-info-400',
        AnalysisStatus::Completed => 'bg-success-100 text-success-600 dark:bg-success-900/30 dark:text-success-400',
        AnalysisStatus::Failed => 'bg-danger-100 text-danger-600 dark:bg-danger-900/30 dark:text-danger-400',
    };
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium $classes"]) }}
>
    {{ $status->getLabel() }}
</span>
