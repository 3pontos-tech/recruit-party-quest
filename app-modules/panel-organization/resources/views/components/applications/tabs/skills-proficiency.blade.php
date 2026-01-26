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
    $skillsByCategory = $skills->groupBy(fn ($skill) => $skill->category->value);
    $skills_categories = $skillsByCategory->count();

    $proficiencyLevels = [
        1 => ['label' => 'Beginner', 'color' => 'danger', 'width' => 20],
        2 => ['label' => 'Basic', 'color' => 'warning', 'width' => 40],
        3 => ['label' => 'Intermediate', 'color' => 'info', 'width' => 60],
        4 => ['label' => 'Advanced', 'color' => 'success', 'width' => 80],
        5 => ['label' => 'Expert', 'color' => 'primary', 'width' => 100],
    ];
@endphp

<x-filament::section>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="bg-success-100 text-success-600 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                >
                    <x-heroicon-o-code-bracket class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-text-high text-lg font-semibold">Skills & Proficiency</h3>
                    <p class="text-text-medium text-sm">Technical and professional competencies</p>
                </div>
            </div>
            @if ($hasSkills)
                <x-filament::badge size="sm" color="success">{{ $skills_total }} skills</x-filament::badge>
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
                                    $proficiencyLevel = $skill->pivot->proficiency_level ?? 1;
                                    $yearsOfExperience = $skill->pivot->years_of_experience ?? 0;
                                    $proficiency = $proficiencyLevels[$proficiencyLevel];
                                @endphp

                                <div class="bg-elevation-02dp border-outline-low rounded-lg border p-4">
                                    {{-- Skill Name and Level --}}
                                    <div class="mb-2 flex items-center justify-between">
                                        <h5 class="text-text-high font-medium">{{ $skill->name }}</h5>
                                        <x-filament::badge size="xs" color="{{ $proficiency['color'] }}" class="p-1">
                                            {{ $proficiency['label'] }}
                                        </x-filament::badge>
                                    </div>

                                    {{-- Proficiency Bar --}}
                                    <div class="space-y-2">
                                        <div class="bg-elevation-01dp h-2 w-full overflow-hidden rounded-full">
                                            <div
                                                class="bg-{{ $proficiency['color'] }}-500 h-full transition-all duration-500"
                                                style="width: {{ $proficiency['width'] }}%"
                                            ></div>
                                        </div>

                                        {{-- Experience Info --}}
                                        <div class="text-text-medium flex items-center justify-between text-xs">
                                            <span>{{ $proficiency['label'] }}</span>
                                            <span>
                                                {{ $yearsOfExperience }}
                                                {{ $yearsOfExperience == 1 ? 'year' : 'years' }} experience
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-elevation-02dp border-outline-low rounded-lg border p-6 text-center">
                        <x-heroicon-o-code-bracket class="text-text-low mx-auto h-12 w-12" />
                        <h4 class="text-text-high mt-2 text-sm font-medium">No skills by category</h4>
                        <p class="text-text-medium mt-1 text-sm">Skills data might not be categorized yet.</p>
                    </div>
                @endforelse
            </div>
        @else
            {{-- No Skills State --}}
            <div class="bg-surface-01dp border-outline-low rounded-lg border p-8 text-center">
                <x-heroicon-o-code-bracket class="text-text-low mx-auto h-16 w-16" />
                <h4 class="text-text-high mt-4 text-lg font-medium">No Skills Listed</h4>
                <p class="text-text-medium mt-2 text-sm">
                    This candidate hasn't added any skills to their profile yet.
                </p>
                <div class="mt-4">
                    <x-filament::button size="sm" color="gray" outlined icon="heroicon-o-plus" disabled>
                        Add Skills
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament::section>
