@php
    use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
@endphp

<div class="flex flex-col gap-16">
    <div class="flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-4 sm:items-start">
            <x-he4rt::heading size="2xl">Confira todas as nossas vagas</x-he4rt::heading>
            <x-he4rt::text>{{ $jobs->total() }} vagas disponíveis</x-he4rt::text>
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
        @foreach ($jobs as $job)
            <x-he4rt::card class="group" wire:key="job-{{ $job->id }}">
                <x-slot:header class="gap-4">
                    <x-he4rt::avatar
                        :src="asset('images/3pontos/logo-chain-white.png')"
                        :alt="$job->team->name"
                        size="lg"
                        :circular="false"
                        class="border-outline-light dark:border-outline-dark h-14 w-14 border"
                    />

                    <div class="flex flex-1 flex-col gap-0.5">
                        <x-he4rt::heading size="2xl" :level="2">
                            {{ $job->post?->title ?? 'Sem título' }}
                        </x-he4rt::heading>
                        <x-he4rt::text class="group-hover:text-text-high transition duration-500">
                            {{ $job->team->name }}
                        </x-he4rt::text>
                    </div>
                </x-slot>

                <x-slot:tags>
                    <x-he4rt::tag
                        :icon="$job->work_arrangement->getIcon()"
                        variant="ghost"
                        class="group-hover:text-text-high transition duration-500"
                    >
                        {{ $job->work_arrangement->getLabel() }}
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
        @endforeach
    </div>

    {{-- Pagination Links --}}
    <div class="mt-8">
        {{ $jobs->links() }}
    </div>
</div>
