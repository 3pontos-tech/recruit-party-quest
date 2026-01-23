<div
    x-data="{
        status: @entangle('status'),
        progress: 0,
        visible: @entangle('visible'),
    }"
    x-on:queued.window="visible = true; status = 'sending'; progress = 5"
    x-on:update-bar.window="progress = $event.detail.value"
    x-on:processing.window="status = 'processing'; progress = 50"
    x-on:finished.window="status = 'finished'; progress = 100"
    x-show="visible"
    x-cloak
    class="space-y-6 pt-4"
>
    <div class="flex items-center justify-between px-2">
        {{-- Step: Sent --}}
        <div class="flex flex-col items-center gap-2">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::PaperAirplane"
                class="h-5 w-5"
                x-bind:class="status !== 'idle' ? 'text-white' : 'text-zinc-500'"
            />
            <span
                class="text-[10px] font-bold tracking-widest uppercase"
                x-bind:class="status !== 'idle' ? 'text-white' : 'text-zinc-500'"
            >
                {{ __('panel-app::pages/onboarding.steps.cv.status_bar.sent') }}
            </span>
        </div>

        {{-- Divider 1 --}}
        <div class="mx-4 h-0.5 flex-1 rounded-full bg-zinc-800">
            <div
                class="h-full bg-white transition-all duration-500"
                x-bind:style="status !== 'idle' ? 'width: 100%' : 'width: 0%'"
            ></div>
        </div>

        {{-- Step: Processing --}}
        <div class="flex flex-col items-center gap-2">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::ArrowPath"
                class="h-5 w-5"
                x-bind:class="['processing', 'finished'].includes(status) ? 'text-white' : 'text-zinc-500'"
                x-bind:class="status === 'processing' ? 'animate-spin' : ''"
            />
            <span
                class="text-[10px] font-bold tracking-widest uppercase"
                x-bind:class="['processing', 'finished'].includes(status) ? 'text-white' : 'text-zinc-500'"
            >
                {{ __('panel-app::pages/onboarding.steps.cv.status_bar.processing') }}
            </span>
        </div>

        {{-- Divider 2 --}}
        <div class="mx-4 h-0.5 flex-1 rounded-full bg-zinc-800">
            <div
                class="h-full bg-white transition-all duration-500"
                x-bind:style="status === 'finished' ? 'width: 100%' : 'width: 0%'"
            ></div>
        </div>

        {{-- Step: Finished --}}
        <div class="flex flex-col items-center gap-2">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::Check"
                class="h-5 w-5"
                x-bind:class="status === 'finished' ? 'text-white' : 'text-zinc-500'"
            />
            <span
                class="text-[10px] font-bold tracking-widest uppercase"
                x-bind:class="status === 'finished' ? 'text-white' : 'text-zinc-500'"
            >
                {{ __('panel-app::pages/onboarding.steps.cv.status_bar.finished') }}
            </span>
        </div>
    </div>

    <div class="relative h-1.5 w-full overflow-hidden rounded-full bg-zinc-800">
        <div
            class="absolute top-0 left-0 h-full bg-white transition-all duration-700 ease-out"
            x-bind:style="'width: ' + progress + '%'"
        ></div>
    </div>
</div>
