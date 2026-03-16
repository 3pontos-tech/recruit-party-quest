<div>
    <div class="pointer-events-none absolute top-0 left-0 -z-10 h-screen overflow-hidden">
        <div class="text-outline-light dark:text-outline-dark h-auto w-full -translate-x-1/3 -translate-y-1/3">
            @include('partials.hourglass')
        </div>
    </div>

    {{-- <section class="py-6 md:py-10"> --}}
    {{-- <div --}}
    {{--
        class="mx-auto flex max-w-7xl flex-col justify-between gap-4 px-4 sm:px-6 md:flex-row md:items-center lg:px-8"
    --}}
    {{-- > --}}
    {{-- <div> --}}
    {{-- <x-he4rt::text class="mb-1">Welcome back</x-he4rt::text> --}}
    {{-- <x-he4rt::heading size="xl"> --}}
    {{-- {{ auth()->user()->name }} --}}
    {{-- </x-he4rt::heading> --}}
    {{-- </div> --}}
    {{-- <div class="flex flex-col gap-4 sm:flex-row"> --}}
    {{-- <x-he4rt::button variant="outline" size="sm" icon="heroicon-o-document-text"> --}}
    {{-- Update Resume --}}
    {{-- </x-he4rt::button> --}}
    {{-- <x-he4rt::button --}}
    {{-- variant="solid" --}}
    {{-- size="sm" --}}
    {{--
        icon="heroicon-o-sparkles"
    --}}
    {{-- icon:trailing="heroicon-o-arrow-right" --}}
    {{-- > --}}
    {{-- AI Career Assistant --}}
    {{-- </x-he4rt::button> --}}
    {{-- </div> --}}
    {{-- </div> --}}
    {{-- </section> --}}

    {{-- <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"> --}}
    {{--
        <hr class="border-outline-light dark:border-outline-dark" />
    --}}
    {{--
        </div>
    --}}

    <section class="py-6 md:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-5">
                {{ $this->headerWidgets }}
            </div>
        </div>
    </section>

    <section class="pb-6 md:pb-10">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 sm:px-6 lg:grid-cols-[1fr_400px] lg:px-8">
            <div class="order-2 lg:order-1">
                <livewire:user-latest-applications />
            </div>
            <div class="order-1 lg:order-2">
                <livewire:profile-card />
            </div>
        </div>
    </section>
    <x-filament-actions::modals />
</div>
