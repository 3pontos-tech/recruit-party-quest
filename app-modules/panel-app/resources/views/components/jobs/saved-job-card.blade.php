@props([
    'job',
    'action' => null,
])

@php
    /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */
    $jobUrl = \He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job->post->slug]);
    $publishedAt = $job->published_at instanceof \DateTimeInterface ? $job->published_at : ($job->published_at ? \Illuminate\Support\Carbon::createFromTimestamp($job->published_at) : null);
@endphp

<div
    {{ $attributes->class('group relative space-y-2.5 p-3 transition duration-200 hover:bg-black/5 dark:hover:bg-white/5') }}
>
    @if ($action)
        {{-- Sits above the stretched link so it stays independently clickable --}}
        <div class="absolute top-2.5 right-2.5 z-10">
            {{ $action }}
        </div>
    @endif

    <div class="space-y-0.5 pr-8">
        <x-he4rt::heading level="3" size="sm" class="text-text-high leading-tight">
            {{-- Stretched link: the ::after overlay makes the whole card navigate to the job --}}
            <a href="{{ $jobUrl }}" class="after:absolute after:inset-0 after:content-['']">
                {{ $job->post->title }}
            </a>
        </x-he4rt::heading>
        <x-he4rt::text size="xs" class="text-text-medium">
            {{ $job->is_confidential ? __('panel-app::filament.confidential.company_name') : $job->team->name }}
        </x-he4rt::text>
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($job->work_arrangement)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-briefcase" size="xs">
                {{ $job->work_arrangement->getLabel() }}
            </x-he4rt::tag>
        @endif

        @if ($job->employment_type)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-clock" size="xs">
                {{ $job->employment_type->getLabel() }}
            </x-he4rt::tag>
        @endif

        @if ($job->work_schedule)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-calendar-days" size="xs">
                {{ $job->work_schedule->getLabel() }}
            </x-he4rt::tag>
        @endif

        @if ($job->experience_level)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-chart-bar" size="xs">
                {{ $job->experience_level->getLabel() }}
            </x-he4rt::tag>
        @endif

        @if ($job->department)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-building-office-2" size="xs">
                {{ $job->department->name }}
            </x-he4rt::tag>
        @endif

        @if ($job->category)
            <x-he4rt::tag variant="ghost" icon="heroicon-o-tag" size="xs">
                {{ $job->category->getLabel() }}
            </x-he4rt::tag>
        @endif
    </div>

    <div class="text-text-medium flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
        @if ($job->salary_range_for_candidates)
            <span class="inline-flex items-center gap-1.5">
                <x-he4rt::icon icon="heroicon-o-currency-dollar" class="size-3.5" />
                {{ $job->salary_range_for_candidates }}
            </span>
        @endif

        @if ($publishedAt)
            <span class="inline-flex items-center gap-1.5">
                <x-he4rt::icon icon="heroicon-o-calendar" class="size-3.5" />
                {{ $publishedAt->format('d/m/Y') }}
            </span>
        @endif

        <span class="inline-flex items-center gap-1.5">
            <x-he4rt::icon icon="heroicon-o-users" class="size-3.5" />
            {{ $job->applications_count ?? 0 }}
            {{ __('panel-app::filament.components.saved_jobs_widget.applications') }}
        </span>
    </div>
</div>
