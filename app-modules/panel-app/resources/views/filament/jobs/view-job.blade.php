<x-filament-panels::page>
    <div class="hp-section mb-0 min-h-0">
        <div class="hp-container mt-6 grid grid-cols-1 items-start gap-8 lg:mt-10 lg:grid-cols-3 lg:gap-12">
            <div class="lg:col-span-2">
                <x-panel-app::jobs.job-description :has-action="true" :jobRequisition="$this->getRecord()" />
            </div>

            <aside class="h-full pb-20 lg:pb-32">
                <div class="sticky top-24 flex flex-col gap-6">
                    <x-panel-app::team.about :team="$this->getRecord()->team" />
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
