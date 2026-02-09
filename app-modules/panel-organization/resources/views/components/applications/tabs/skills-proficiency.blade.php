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

<x-filament::section icon="heroicon-o-code-bracket" icon-color="success">
    <x-slot name="heading">
        {{ __('panel-organization::view.tabs.skills_title') }}
    </x-slot>

    <x-slot name="description">
        {{ __('panel-organization::view.tabs.skills_subtitle') }}
    </x-slot>

    @if ($hasSkills)
        <x-slot name="afterHeader">
            <x-he4rt::tag size="sm">
                {{ trans_choice('panel-organization::view.tabs.skills_count', $skills_total, ['count' => $skills_total]) }}
            </x-he4rt::tag>
        </x-slot>
    @endif

    @if ($hasSkills)
        {{-- Skills by Category --}}
        @forelse ($skillsByCategory as $category => $categorySkills)
            <div class="mb-5">
                {{-- Category Header --}}
                <h4 class="text-text-high mb-4 text-base font-semibold capitalize">
                    {{ str_replace('_', ' ', strtolower($category)) }}
                </h4>

                {{-- Skills Grid --}}
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
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
                                <x-he4rt::tag size="xs">
                                    {{ __('panel-organization::view.proficiency.' . $proficiencyLevel) }}
                                </x-he4rt::tag>
                            </div>

                            {{-- Proficiency Bar --}}
                            <div class="space-y-2">
                                <div class="bg-elevation-01dp h-2 w-full overflow-hidden rounded-full">
                                    <div
                                        class="{{ $proficiency['color'] }} h-full transition-all duration-500"
                                        style="width: {{ $proficiency['width'] }}%"
                                    ></div>
                                </div>

                                {{-- Experience Info --}}
                                <div class="text-text-medium flex items-center justify-between text-xs leading-relaxed">
                                    <span>
                                        {{ __('panel-organization::view.proficiency.' . $proficiencyLevel) }}
                                    </span>
                                    <span>
                                        {{ trans_choice('panel-organization::view.time.year', $yearsOfExperience ?? 0, ['count' => $yearsOfExperience ?? 0]) }}
                                        {{ __('panel-organization::view.tabs.experience_label') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <x-filament::section :secondary="true">
                <div class="py-4 text-center">
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
            </x-filament::section>
        @endforelse
    @else
        {{-- No Skills State --}}
        <x-filament::section :secondary="true">
            <div class="py-4 text-center">
                <x-he4rt::icon
                    :icon="\Filament\Support\Icons\Heroicon::CodeBracket"
                    size="lg"
                    class="text-text-low mx-auto"
                />
                <h4 class="text-text-high mt-4 text-lg font-medium">
                    {{ __('panel-organization::view.tabs.no_skills_listed') }}
                </h4>
            </div>
        </x-filament::section>
    @endif
</x-filament::section>
