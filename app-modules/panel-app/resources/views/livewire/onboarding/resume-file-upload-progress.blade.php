<div @class(['space-y-6', 'hidden' => ! $this->visible])>
    <div class="flex items-center justify-between px-2">
        <!-- Step: Sent -->
        <x-panel-app::shared.step
            label="Sent"
            :active="$status !== 'idle'"
            :completed="in_array($status, ['processing', 'finished'])"
            :icon="\Filament\Support\Icons\Heroicon::PaperAirplane"
        />

        <!-- Divider -->
        <div class="mx-4 h-0.5 flex-1 rounded-full bg-zinc-800">
            <div
                @class([
                    'h-full bg-white transition-all duration-1000',
                    'w-full' => $status !== 'idle',
                    'w-0' => $status === 'idle',
                ])
            ></div>
        </div>

        <!-- Step: Processing -->
        <x-panel-app::shared.step
            label="Processing"
            :active="in_array($status, ['processing', 'finished'])"
            :completed="$status === 'finished'"
            :icon="\Filament\Support\Icons\Heroicon::ArrowPath"
            :animate="$status === 'processing'"
        />

        <!-- Divider -->
        <div class="mx-4 h-0.5 flex-1 rounded-full bg-zinc-800">
            <div
                @class([
                    'h-full bg-white transition-all duration-1000',
                    'w-full' => in_array($status, ['processing', 'finished']),
                    'w-0' => ! in_array($status, ['processing', 'finished']),
                ])
            ></div>
        </div>

        <!-- Step: Finished -->
        <x-panel-app::shared.step
            label="Finished"
            :active="$status === 'finished'"
            :completed="$status === 'finished'"
            :icon="\Filament\Support\Icons\Heroicon::Check"
        />
    </div>

    <!-- Progress bar -->
    <div class="relative h-2 w-full overflow-hidden rounded-full border border-zinc-700 bg-zinc-800 shadow-inner">
        <div
            class="absolute top-0 left-0 flex h-full items-center justify-end overflow-hidden bg-white transition-all duration-700 ease-out"
            style="width: {{ $progress }}%"
        >
            <div class="progress-bar-shine absolute inset-0"></div>
        </div>
    </div>
</div>
