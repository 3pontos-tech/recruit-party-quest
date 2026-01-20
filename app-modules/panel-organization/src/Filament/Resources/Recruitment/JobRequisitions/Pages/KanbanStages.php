<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use Livewire\Attributes\Locked;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;

class KanbanStages extends BoardResourcePage
{
    use InteractsWithBoard;

    #[Locked]
    public ?string $requisitionId = null;

    protected static string $resource = JobRequisitionResource::class;

    /**
     * Initialize component state and panel UI when the page mounts.
     *
     * Sets the component's requisitionId from the route `record` parameter and ensures
     * the current Filament panel's sidebar is collapsible on desktop.
     */
    public function mount(): void
    {
        $this->requisitionId = request()->route('record');

        filament()
            ->getCurrentPanel()
            ->sidebarCollapsibleOnDesktop();
    }

    /**
     * Close the application's sidebar when the component is rendered.
     *
     * Triggers the client-side sidebar store to ensure the sidebar is closed after render.
     */
    public function rendered(): void
    {
        $this->js('$store.sidebar.close()');
    }

    /**
     * Configure and return a Kanban board populated with columns for the job requisition's stages.
     *
     * @param Board $board The board instance to configure.
     * @return Board The configured board: columns built from the requisition's stages (stage id as identifier, label and color from stage data), card schema showing a `status` badge, an edit action bound to the JobRequisition model, a query restricted to Application records for the requisition, and `current_stage_id` set as the column identifier.
     */
    public function board(Board $board): Board
    {
        // 1. Columns precisam de identificação única. (usar stage->getKey())
        //
        // 2. Applications with some validations.

        $jobRequisition = JobRequisition::query()
            ->with([
                'stages',
                'post',
            ])
            ->findOrFail($this->requisitionId);

        $columns = collect($jobRequisition->stages)
            ->map(fn (Stage $stage) => Column::make($stage->id)
                ->label(sprintf('%s (%s)', $stage->name, $stage->stage_type->getLabel()))
                ->color($stage->stage_type->getColor())
            )->toArray();

        return $board
            ->recordTitleAttribute('candidate.user.name')
            ->cardSchema(fn (Schema $schema) => $schema
                ->components([
                    TextEntry::make('status')->badge(),
                ])
            )
            ->cardActions([
                EditAction::make()->model(JobRequisition::class),
            ])
            ->query(
                Application::query()
                    ->where('requisition_id', $jobRequisition->getKey())
            )
            ->columnIdentifier('current_stage_id')
            ->columns($columns);
    }
}