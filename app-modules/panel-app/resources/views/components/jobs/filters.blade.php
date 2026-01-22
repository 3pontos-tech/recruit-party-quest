<aside class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-he4rt::icon size="xs" :icon="\Filament\Support\Icons\Heroicon::Funnel" class="text-muted-foreground" />
            <x-he4rt::heading size="xs" level="2">Filters</x-he4rt::heading>
        </div>
    </div>

    <div class="border-border border-b pb-4">
        <x-he4rt::heading size="2xs" class="py-2">Work Arrangement</x-he4rt::heading>
        <div class="space-y-2 pt-2">
            <x-he4rt::checkbox label="Remote" id="work-remote" />
            <x-he4rt::checkbox label="Hybrid" id="work-hybrid" />
            <x-he4rt::checkbox label="On-site" id="work-onsite" />
        </div>
    </div>

    <div class="border-border border-b pb-4">
        <x-he4rt::heading size="2xs" class="py-2">Employment Type</x-he4rt::heading>
        <div class="space-y-2 pt-2">
            <x-he4rt::checkbox label="Full Time" id="type-full-time" />
            <x-he4rt::checkbox label="Contract" id="type-contract" />
            <x-he4rt::checkbox label="Internship" id="type-internship" />
        </div>
    </div>
</aside>
