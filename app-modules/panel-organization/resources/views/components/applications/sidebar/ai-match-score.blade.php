@props([
    'record',
])

@php
    // Visual placeholder data - as requested, this will be replaced with actual logic later
    $matchScore = 87; // Mock score
    $matchBreakdown = [
        'Skills Match' => ['score' => 92, 'color' => 'success'],
        'Experience' => ['score' => 85, 'color' => 'success'],
        'Education' => ['score' => 90, 'color' => 'success'],
        'Location' => ['score' => 78, 'color' => 'warning'],
        'Availability' => ['score' => 95, 'color' => 'success'],
    ];

    // Determine overall color based on score
    $overallColor = $matchScore >= 80 ? 'success' : ($matchScore >= 60 ? 'warning' : 'danger');
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h3 class="text-text-high text-sm font-semibold">{{ __('panel-organization::view.ai.title') }}</h3>
        <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::Sparkles" size="sm" class="text-icon-medium" />
    </div>

    {{-- Main Score Display --}}
    <div class="flex flex-col space-y-2 text-center">
        <div class="relative inline-flex items-center justify-center">
            {{-- Circular Progress Ring --}}
            <svg class="h-20 w-20 -rotate-90 transform" viewBox="0 0 32 32">
                {{-- Background Ring --}}
                <circle
                    cx="16"
                    cy="16"
                    r="14"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    class="text-outline-low"
                />
                {{-- Progress Ring --}}
                <circle
                    cx="16"
                    cy="16"
                    r="14"
                    stroke="currentColor"
                    stroke-width="2"
                    fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="87.96"
                    stroke-dashoffset="{{ 87.96 - (87.96 * $matchScore) / 100 }}"
                    class="{{ $overallColor === 'success' ? 'text-green-500' : ($overallColor === 'warning' ? 'text-yellow-500' : 'text-red-500') }} transition-all duration-500"
                />
            </svg>

            {{-- Score Text --}}
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-text-high text-2xl font-bold">{{ $matchScore }}%</span>
            </div>
        </div>

        <x-he4rt::tag variant="solid" size="lg">
            @if ($matchScore >= 80)
                {{ __('panel-organization::view.ai.excellent') }}
            @elseif ($matchScore >= 60)
                {{ __('panel-organization::view.ai.good') }}
            @else
                {{ __('panel-organization::view.ai.fair') }}
            @endif
        </x-he4rt::tag>
    </div>

    {{-- Score Breakdown --}}
    <div class="space-y-3">
        <h4 class="text-text-medium text-xs font-semibold tracking-wider uppercase">
            {{ __('panel-organization::view.ai.breakdown') }}
        </h4>

        <div class="space-y-3">
            @foreach ($matchBreakdown as $category => $data)
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-text-high text-xs font-medium">{{ $category }}</span>
                        <span class="text-text-high text-xs font-semibold">{{ $data['score'] }}%</span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="bg-elevation-01dp h-2 w-full overflow-hidden rounded-full">
                        <div
                            class="{{ $data['color'] === 'success' ? 'bg-green-500' : ($data['color'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }} h-full transition-all duration-500"
                            style="width: {{ $data['score'] }}%"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Key Insights --}}
    <div class="border-outline-low border-t pt-3">
        <h4 class="text-text-medium mb-2 text-xs font-semibold tracking-wider uppercase">
            {{ __('panel-organization::view.ai.key_insights') }}
        </h4>

        <div class="space-y-2">
            <div class="flex items-start gap-2">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::CheckCircle"
                    size="sm"
                    class="mt-0.5 shrink-0 text-green-500"
                />
                <span class="text-text-high text-xs">{{ __('panel-organization::view.ai.strong_skills') }}</span>
            </div>

            <div class="flex items-start gap-2">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::CheckCircle"
                    size="sm"
                    class="mt-0.5 shrink-0 text-green-500"
                />
                <span class="text-text-high text-xs">{{ __('panel-organization::view.ai.relevant_experience') }}</span>
            </div>

            <div class="flex items-start gap-2">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::ExclamationTriangle"
                    size="sm"
                    class="mt-0.5 shrink-0 text-yellow-500"
                />
                <span class="text-text-high text-xs">{{ __('panel-organization::view.ai.location_flag') }}</span>
            </div>
        </div>
    </div>

    {{-- AI Note --}}
    <div class="bg-elevation-02dp border-outline-low rounded-md border p-3">
        <div class="flex items-start gap-2">
            <x-he4rt::icon
                :icon="\Filament\Support\Icons\Heroicon::InformationCircle"
                size="sm"
                class="text-primary mt-0.5 shrink-0"
            />
            <p class="text-text-medium text-xs">
                <span class="text-primary font-semibold">{{ __('panel-organization::view.ai.analysis_label') }}</span>
                {{ __('panel-organization::view.ai.analysis_text') }}
            </p>
        </div>
    </div>
</div>
