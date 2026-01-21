<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions\StateTransitionAction;
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

    public function mount(): void
    {
        $this->requisitionId = request()->route('record');

        filament()
            ->getCurrentPanel()
            ->sidebarCollapsibleOnDesktop();
    }

    public function rendered(): void
    {
        $this->js('$store.sidebar.close()');
    }

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
                    TextEntry::make('status')
                        ->label('Application Status')
                        ->badge(),
                    TextEntry::make('candidate.total_work_experience_years'),
                    TextEntry::make('tracking_code'),
                ])
            )
            ->cardActions([
                StateTransitionAction::make(),
                EditAction::make()
                    ->model(Application::class),
            ])
            ->query(
                Application::query()
                    ->where('requisition_id', $jobRequisition->getKey())
            )
            ->columnIdentifier('current_stage_id')
            ->columns($columns);
    }
}
