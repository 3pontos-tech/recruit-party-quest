@props(['language' => null])

@php
    $colors = [
        'TypeScript' => 'bg-blue-500/20 text-blue-600 dark:text-blue-400',
        'JavaScript' => 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-400',
        'Python' => 'bg-green-500/20 text-green-600 dark:text-green-400',
        'Go' => 'bg-cyan-500/20 text-cyan-600 dark:text-cyan-400',
        'Rust' => 'bg-orange-500/20 text-orange-600 dark:text-orange-400',
        'Java' => 'bg-red-500/20 text-red-600 dark:text-red-400',
        'C#' => 'bg-purple-500/20 text-purple-600 dark:text-purple-400',
        'Ruby' => 'bg-red-500/20 text-red-600 dark:text-red-400',
        'PHP' => 'bg-indigo-500/20 text-indigo-600 dark:text-indigo-400',
        'Swift' => 'bg-orange-500/20 text-orange-600 dark:text-orange-400',
        'Kotlin' => 'bg-purple-500/20 text-purple-600 dark:text-purple-400',
        'Dart' => 'bg-teal-500/20 text-teal-600 dark:text-teal-400',
        'Vue' => 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        'CSS' => 'bg-pink-500/20 text-pink-600 dark:text-pink-400',
        'HTML' => 'bg-orange-500/20 text-orange-600 dark:text-orange-400',
        'Shell' => 'bg-gray-500/20 text-gray-600 dark:text-gray-400',
    ];
    $colorClass = $colors[$language] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
@endphp

@if ($language)
    <span
        {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium $colorClass"]) }}
    >
        <span class="h-2 w-2 rounded-full bg-current"></span>
        {{ $language }}
    </span>
@endif
