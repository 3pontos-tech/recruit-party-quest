@php
    use Illuminate\Support\Js;
@endphp

@props([
    'job',
    'variant' => 'icon-only',
    //'icon-only'|'icon-text''size' => 'sm',
])

@php
    /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */

    // Prepare enhanced job data with all 11 fields
    $jobId = (string) $job->getKey();
    $jobTitle = $job->post?->title ?? 'Sem título';
    $jobCompany = $job->team->name;
    $jobUrl = He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job]);

    // Format salary range
    $salaryRange = null;
    if ($job->show_salary_to_candidates && ! is_null($job->salary_range_min) && ! is_null($job->salary_range_max)) {
        $salaryRange = $job->salary_currency . ' ' . number_format($job->salary_range_min, 0, ',', '.') . ' - ' . number_format($job->salary_range_max, 0, ',', '.');
    }

    $jobData = [
        'id' => $jobId,
        'title' => $jobTitle,
        'company' => $jobCompany,
        'companyLogo' => asset('images/3pontos/logo-chain-white.png'),
        'url' => $jobUrl,
        'workArrangement' => $job->work_arrangement->getLabel(),
        'workArrangementIcon' => $job->work_arrangement->getIcon(),
        'workArrangementColor' => $job->work_arrangement->getColor(),
        'employmentType' => $job->employment_type?->getLabel() ?? 'Full Time',
        'contractType' => $job->employment_type?->value ?? 'FullTimeEmployee',
        'salaryRange' => $salaryRange,
        'department' => $job->department?->name,
        'experienceLevel' => $job->experience_level?->getLabel(),
        'category' => $job->category?->getLabel(),
        'publishedAt' => $job->published_at instanceof \DateTimeInterface ? $job->published_at->toIso8601String() : null,
        'applicationsCount' => $job->applications_count ?? 0,
    ];
@endphp

@if ($variant === 'icon-only')
    {{-- Icon Only Variant --}}
    <button
        type="button"
        x-on:click.stop.prevent="$store.savedJobs.toggle({{ Js::from($jobData) }})"
        class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 flex size-8 items-center justify-center rounded-lg border p-2 transition duration-200"
        :aria-label="$store.savedJobs.isSaved({{ Js::from($jobId) }}) ? '{{ __('panel-app::filament.components.bookmark_button.remove_saved') }}' : '{{ __('panel-app::filament.components.bookmark_button.save_job') }}'"
    >
        <x-he4rt::icon
            icon="heroicon-s-bookmark"
            class="text-primary size-4"
            x-show="$store.savedJobs.isSaved({{ Js::from($jobId) }})"
            x-cloak
        />
        <x-he4rt::icon
            icon="heroicon-o-bookmark"
            class="size-4"
            x-show="!$store.savedJobs.isSaved({{ Js::from($jobId) }})"
        />
    </button>
@else
    {{-- Icon + Text Variant --}}
    <button
        type="button"
        x-on:click.stop.prevent="$store.savedJobs.toggle({{ Js::from($jobData) }})"
        class="border-outline-light dark:border-outline-dark hover:border-outline-high/32 flex w-full flex-1 items-center justify-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition duration-200"
        :aria-label="$store.savedJobs.isSaved({{ Js::from($jobId) }}) ? '{{ __('panel-app::filament.components.bookmark_button.remove_saved') }}' : '{{ __('panel-app::filament.components.bookmark_button.save_job') }}'"
    >
        <x-he4rt::icon
            icon="heroicon-s-bookmark"
            class="size-4 text-yellow-300"
            x-show="$store.savedJobs.isSaved({{ Js::from($jobId) }})"
            x-cloak
        />
        <span x-show="$store.savedJobs.isSaved({{ Js::from($jobId) }})" x-cloak>
            {{ __('panel-app::filament.components.bookmark_button.saved') }}
        </span>

        <x-he4rt::icon
            icon="heroicon-o-bookmark"
            class="size-4"
            x-show="!$store.savedJobs.isSaved({{ Js::from($jobId) }})"
        />
        <span x-show="! $store.savedJobs.isSaved({{ Js::from($jobId) }})">
            {{ __('panel-app::filament.components.bookmark_button.save') }}
        </span>
    </button>
@endif
