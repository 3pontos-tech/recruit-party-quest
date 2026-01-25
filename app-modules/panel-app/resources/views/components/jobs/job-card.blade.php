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
    <x-slot:title class="flex items-start justify-between gap-2">
        <div class="flex gap-4">
            <x-he4rt::avatar
                :src="asset('images/3pontos/logo-chain-white.png')"
                :alt="$job->team->name"
                size="lg"
                :circular="false"
                class="border-outline-light dark:border-outline-dark h-12 w-12 border"
            />

            <div class="min-w-0">
                <span class="text-foreground hover:text-primary line-clamp-1 text-lg font-semibold transition-colors">
                    {{ $job->post?->title ?? 'No Title' }}
                </span>
                <x-he4rt::text size="sm" class="text-muted-foreground">{{ $job->team->name }}</x-he4rt::text>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-1">
            <x-he4rt::button
                variant="outline"
                size="sm"
                class="size-8"
                :icon="\Filament\Support\Icons\Heroicon::Heart"
            />
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

    <div class="text-muted-foreground mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
        <x-he4rt::tag
            :icon="\Filament\Support\Icons\Heroicon::MapPin"
            variant="ghost"
            class="group-hover:text-text-high transition duration-500"
        >
            {{ $job->team->address->first()?->city ?? 'Remote' }}
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
    </div>
</x-he4rt::card>
