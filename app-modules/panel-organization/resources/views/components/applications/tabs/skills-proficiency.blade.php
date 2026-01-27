@props([
    'record',
])

@php
    /** @var \He4rt\Applications\Models\Application $record */
    /** @var \He4rt\Candidates\Models\Candidate $candidate */
    $candidate = $record->candidate;
    $skills = $candidate->skills()->get();
    $hasSkills = $skills->isNotEmpty();

    $skills_total = $skills->count();
    $skill_advanced_plus = $skills->filter(fn ($skill) => ($skill->pivot->proficiency_level ?? 0) >= 4)->count();
    $skills_avg_years = round($skills->avg(fn ($skill) => $skill->pivot->years_of_experience ?? 0), 1);
    $skillsByCategory = $skills->groupBy(fn ($skill) => $skill->category?->value ?? 'uncategorized');
    $skills_categories = $skillsByCategory->count();

    $proficiencyLevels = [
        1 => ['color' => 'bg-red-500', 'width' => 20],
        2 => ['color' => 'bg-yellow-500', 'width' => 40],
        3 => ['color' => 'bg-blue-500', 'width' => 60],
        4 => ['color' => 'bg-green-500', 'width' => 80],
        5 => ['color' => 'bg-primary-500', 'width' => 100],
    ];
@endphp

<div class="bg-surface-01dp border-outline-low space-y-4 rounded-lg border p-4">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="bg-success-100 text-success-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                >
                    <x-he4rt::icon :icon="\Filament\Support\Icons\Heroicon::CodeBracket" size="sm" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">
                        {{ __('panel-organization::view.tabs.skills_title') }}
                    </h3>
                    <p class="text-text-medium text-sm">{{ __('panel-organization::view.tabs.skills_subtitle') }}</p>
                </div>
            </div>
            @if ($hasSkills)
                <x-he4rt::tag size="sm" variant="solid">
                    {{ trans_choice('panel-organization::view.tabs.skills_count', $skills_total, ['count' => $skills_total]) }}
                </x-he4rt::tag>
            @endif
        </div>

        @if ($hasSkills)
            {{-- Skills by Category --}}
            <div class="space-y-6">
                @forelse ($skillsByCategory as $category => $categorySkills)
                    <div class="space-y-4">
                        {{-- Category Header --}}
                        <div class="flex items-center gap-2">
                            <h4 class="text-text-high text-base font-semibold capitalize">
                                {{ str_replace('_', ' ', strtolower($category)) }}
                            </h4>
                        </div>

                        {{-- Skills Grid --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($categorySkills as $skill)
                                @php
                                    $proficiencyLevel = (int) ($skill->pivot->proficiency_level ?? 1);
                                    $proficiencyLevel = max(array_key_first($proficiencyLevels), min($proficiencyLevel, array_key_last($proficiencyLevels)));
                                    $yearsOfExperience = $skill->pivot->years_of_experience ?? 0;
                                    $proficiency = $proficiencyLevels[$proficiencyLevel];
                                @endphp

                                <div class="bg-elevation-02dp border-outline-low rounded-lg border p-4">
                                    {{-- Skill Name and Level --}}
                                    <div class="mb-2 flex items-center justify-between">
                                        <h5 class="text-text-high font-medium">{{ $skill->name }}</h5>
                                        <x-he4rt::tag size="xs" variant="solid" class="p-1">
                                            {{ __('panel-organization::filament.proficiency.' . $proficiencyLevel) }}
                                        </x-he4rt::tag>
                                    </div>

                                    {{-- Proficiency Bar --}}
                                    <div class="space-y-2">
                                        <div class="bg-elevation-01dp h-2 w-full overflow-hidden rounded-full">
                                            <div
                                                class="bg-{{ $proficiency['color'] }} h-full transition-all duration-500"
                                                style="width: {{ $proficiency['width'] }}%"
                                            ></div>
                                        </div>

                                        {{-- Experience Info --}}
                                        <div class="text-text-medium flex items-center justify-between text-xs">
                                            <span>
                                                {{ __('panel-organization::filament.proficiency.' . $proficiencyLevel) }}
                                            </span>
                                            <span>
                                                @php
                                                    $skillYears = (int) ($yearsOfExperience ?? 0);
                                                    $skillMonths = 0; // months not tracked separately in pivot; keep months=0
                                                @endphp

                                                @if ($skillYears > 0 && $skillMonths > 0)
                                                    {{ trans_choice('panel-organization::view.time.year', $skillYears, ['count' => $skillYears]) }}
                                                    {{ __('panel-organization::view.time.and') }}
                                                    {{ trans_choice('panel-organization::view.time.month', $skillMonths, ['count' => $skillMonths]) }}
                                                @elseif ($skillYears > 0)
                                                    {{ trans_choice('panel-organization::view.time.year', $skillYears, ['count' => $skillYears]) }}
                                                @else
                                                    {{ trans_choice('panel-organization::view.time.month', $skillMonths, ['count' => $skillMonths]) }}
                                                @endif

                                                {{ __('panel-organization::view.tabs.experience_label') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-elevation-02dp border-outline-low rounded-lg border p-6 text-center">
                        <x-he4rt::icon
                            :icon="\Filament\Support\Icons\Heroicon::CodeBracket"
                            size="lg"
                            class="text-text-low mx-auto"
                        />
                        <h4 class="text-text-high mt-2 text-sm font-medium">
                            {{ __('panel-organization::view.tabs.no_skills_by_category') }}
                        </h4>
                        <p class="text-text-medium mt-1 text-sm">
                            {{ __('panel-organization::view.tabs.no_skills_listed') }}
                        </p>
                    </div>
                @endforelse
            </div>
        @else
            {{-- No Skills State --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::CodeBracket"
                    size="xl"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.no_skills_listed') }}
                </h4>
                <p class="text-text-medium mt-2 text-sm">
                    {{ __('panel-organization::view.tabs.no_skills_listed') }}
                </p>
                <div class="mt-4">
                    <x-he4rt::button
                        size="sm"
                        variant="outline"
                        :icon="\Filament\Support\Icons\Heroicon::Plus"
                        disabled
                    >
                        {{ __('panel-organization::view.tabs.add_skills') }}
                    </x-he4rt::button>
                </div>
            </div>
        @endif
    </div>
</div>
