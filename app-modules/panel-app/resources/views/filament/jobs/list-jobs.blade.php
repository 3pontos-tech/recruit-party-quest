<x-filament-panels::page>
    <livewire:search-jobs :query="$this->getFilteredSortedTableQuery()->toRawSql()" />
</x-filament-panels::page>
