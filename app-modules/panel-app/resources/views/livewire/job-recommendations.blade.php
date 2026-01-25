@php
    use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
@endphp

<div class="flex flex-col gap-16">
    <div class="flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-4 sm:items-start">
            <x-he4rt::heading size="2xl">Confira todas as nossas vagas</x-he4rt::heading>
            <x-he4rt::text>{{ $this->jobs->total() }} vagas disponíveis</x-he4rt::text>
        </div>

        <div class="flex items-center gap-8">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar vagas..."
                class="border-outline-dark bg-elevation-01dp rounded-md p-2 text-sm"
            />

            <select wire:model.live="workModel" class="border-outline-dark bg-elevation-01dp rounded-md p-2 text-sm">
                <option value="">Modelo de trabalho</option>
                @foreach (WorkArrangementEnum::cases() as $case)
                    <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3" wire:transition>
        @forelse ($this->jobs as $job)
            <x-panel-app::jobs.job-card :job="$job" wire:key="job-{{ $job->id }}" />
        @empty
            <x-he4rt::card
                :interactive="false"
                class="col-span-3 flex flex-col items-center justify-center border-dashed p-12 text-center"
            >
                <x-he4rt::badge icon="heroicon-o-magnifying-glass" class="bg-elevation-05dp rounded-full border-0" />
                <x-he4rt::heading size="sm">No jobs found</x-he4rt::heading>
                <x-he4rt::text size="sm" class="text-muted-foreground mt-1">
                    Try adjusting your search or filters to find what you're looking for.
                </x-he4rt::text>
                <x-he4rt::button wire:click="$set('search', '')" variant="outline" size="sm" class="mt-4">
                    Clear all filters
                </x-he4rt::button>
            </x-he4rt::card>
        @endforelse
    </div>

    {{-- Pagination Links --}}
    <div class="mt-8">
        {{ $this->jobs->links() }}
    </div>
</div>
