@php
    $jobRequisition = $this->getRecord()->requisition;
    $currentStage = $this->getRecord()->currentStage;
    $stages = $jobRequisition->stages;
    $team = $jobRequisition->team;
@endphp

<x-filament-panels::page>
    <div class="mt-6 grid grid-cols-1 items-start gap-8 lg:mt-10 lg:grid-cols-3 lg:gap-12">
        <div class="lg:col-span-2">
            <x-panel-app::jobs.job-description :$jobRequisition />
        </div>

        <aside class="h-full pb-20 lg:pb-32">
            <div class="sticky top-24 flex flex-col gap-6">
                <x-panel-app::applications.stage-timeline :$stages :$currentStage />
                <x-panel-app::team.about :team="$team" />
            </div>
        </aside>
    </div>
</x-filament-panels::page>
