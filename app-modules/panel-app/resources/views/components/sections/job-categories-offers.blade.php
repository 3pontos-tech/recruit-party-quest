@php
    use He4rt\Recruitment\Requisitions\Enums\JobCategoryEnum;
@endphp

<section class="hp-section relative z-10" id="categories">
    <div class="hp-container flex flex-col gap-16">
        <div class="mb-0 grid grid-cols-1 items-start gap-x-16 sm:grid-cols-[1fr_7fr]">
            <div
                x-data="{ visible: false }"
                x-intersect.once="visible = true"
                class="mb-4 flex items-center justify-center sm:justify-start"
            >
                <x-he4rt::animate-block duration="700">
                    <x-he4rt::section-title size="lg">
                        {{ __('panel-app::filament.categories.section_title') }}
                    </x-he4rt::section-title>
                </x-he4rt::animate-block>
            </div>

            <div>
                <x-he4rt::headline class="mx-0">
                    <x-slot:title>
                        {{ __('panel-app::filament.categories.headline_title') }}
                    </x-slot>
                    <x-slot:description>
                        {{ __('panel-app::filament.categories.headline_description') }}
                    </x-slot>
                </x-he4rt::headline>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (JobCategoryEnum::cases() as $category)
                <x-he4rt::card
                    :href="route('filament.app.resources.job-requisitions.index', ['category' => $category->value])"
                    class="group p-8"
                    density="compact"
                >
                    <x-slot:icon class="gap-4">
                        <x-he4rt::badge :icon="$category->getIcon()" />
                    </x-slot>

                    <x-slot:title>{{ $category->getLabel() }}</x-slot>

                    <x-slot:description>
                        {{ $category->getDescription() }}
                    </x-slot>

                    <x-slot:footer class="mt-auto border-t-0 pt-0">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <x-he4rt::text>
                                    {{ __('panel-app::filament.categories.average_salary') }}
                                </x-he4rt::text>
                                <x-he4rt::heading :level="4" size="2xl">
                                    R$ {{ number_format($category->getAverageSalary(), 0, ',', '.') }}
                                </x-he4rt::heading>
                            </div>
                            <div>
                                <x-he4rt::icon
                                    icon="heroicon-o-chevron-right"
                                    class="transition-transform group-hover:translate-x-1"
                                />
                            </div>
                        </div>
                    </x-slot>
                </x-he4rt::card>
            @endforeach
        </div>
    </div>
</section>
