@props([
    'job',
])

@php
    /** @var \He4rt\Recruitment\Requisitions\Models\JobRequisition $job */
@endphp

<x-he4rt::card
    class="group"
    href="{{ He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource::getUrl('view', ['record' => $job]) }}"
>
    <x-slot:header class="gap-4">
        <x-he4rt::avatar
            :src="asset('images/3pontos/logo-chain-white.png')"
            :alt="$job->team->name"
            size="lg"
            :circular="false"
            class="group-hover:border-outline-high/32 border-outline-light dark:border-outline-dark h-14 w-14 border transition duration-500"
        />

        <div class="flex flex-1 flex-col gap-0.5">
            <x-he4rt::heading size="2xl" :level="2">
                {{ $job->post?->title ?? 'Sem título' }}
            </x-he4rt::heading>
            <x-he4rt::text class="group-hover:text-text-high transition duration-500">
                {{ $job->team->name }}
            </x-he4rt::text>
        </div>

        <div class="flex shrink-0">
            <x-he4rt::button
                variant="outline"
                size="sm"
                class="size-8"
                :icon="\Filament\Support\Icons\Heroicon::Share"
            />
        </div>
    </x-slot>

    <x-slot:description class="mt-3 leading-relaxed">
        {{ $job->post?->summary }}
    </x-slot>

    <x-slot:tags class="gap-x-8 gap-y-4">
        <x-he4rt::tag
            :icon="$job->work_arrangement->getIcon()"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ $job->work_arrangement->getLabel() }}
        </x-he4rt::tag>
        <x-he4rt::tag
            :icon="\Filament\Support\Icons\Heroicon::BuildingOffice2"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ $job->department?->name ?? 'Engineering' }}
        </x-he4rt::tag>
        <x-he4rt::tag
            :icon="\Filament\Support\Icons\Heroicon::Home"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ $job->employment_type?->getLabel() ?? 'Full Time' }}
        </x-he4rt::tag>
        <x-he4rt::tag
            :icon="\Filament\Support\Icons\Heroicon::CurrencyDollar"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ $job->salary_currency }}&nbsp;{{ number_format($job->salary_range_min, 0, ',', '.') }} -
            {{ number_format($job->salary_range_max, 0, ',', '.') }}
        </x-he4rt::tag>
        <x-he4rt::tag
            :icon="\Filament\Support\Icons\Heroicon::Users"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ 1 }} positions
        </x-he4rt::tag>
    </x-slot>

    <x-slot:footer>
        <div class="flex items-center justify-between">
            <x-he4rt::tag
                icon="heroicon-o-user"
                variant="ghost"
                class="group-hover:text-text-high gap-2 transition duration-500"
            >
                {{ $job->applications_count }} aplicações
            </x-he4rt::tag>
            <x-he4rt::tag
                icon="heroicon-o-clock"
                variant="ghost"
                class="group-hover:text-text-high gap-2 transition duration-500"
            >
                {{ $job->created_at->format('d/m/Y') }}
            </x-he4rt::tag>
        </div>
    </x-slot>
</x-he4rt::card>
