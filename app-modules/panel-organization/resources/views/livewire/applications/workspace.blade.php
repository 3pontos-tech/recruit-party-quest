<div class="space-y-4">
    @if ($this->requisition === null)
        @include('panel-organization::livewire.applications.workspace.overview')
    @else
        @include('panel-organization::livewire.applications.workspace.requisition')
    @endif
</div>
