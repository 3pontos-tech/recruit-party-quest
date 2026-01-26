<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions\StateTransitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions\ViewCandidateAction;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Filament\Schema\KanbanColumn;
use He4rt\Recruitment\Stages\Models\Stage;
use Livewire\Attributes\Locked;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;

class KanbanStages extends BoardResourcePage
{
    use InteractsWithBoard;

    #[Locked]
    public ?string $requisitionId = null;

    protected static string $resource = JobRequisitionResource::class;

    public function getSubNavigation(): array
    {
        return [
            NavigationItem::make(__('recruitment::filament.requisition.kanban.nav.edit_stages'))
                ->icon(Heroicon::OutlinedPencilSquare)
                ->url(JobRequisitionResource::getUrl('edit', ['record' => $this->requisitionId]))
                ->label(__('recruitment::filament.requisition.kanban.nav.edit_label')),
            NavigationItem::make(__('recruitment::filament.requisition.kanban.nav.kanban_stages'))
                ->url(self::getUrl(['record' => $this->requisitionId]))
                ->isActiveWhen(fn () => true)
                ->icon(Heroicon::Calendar)
                ->activeIcon(Heroicon::Calendar),
        ];
    }

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

        $columns = collect($jobRequisition->stages->sortBy('display_order'))
            ->map(fn (Stage $stage) => KanbanColumn::make($stage->id)
                ->label($stage->name)
                ->icon($stage->stage_type->getIcon())
                ->color($stage->stage_type->getColor())
                ->description($stage->stage_type)
                ->recruiters($stage->interviewers)
                ->hidden($stage->hidden)
            )->toArray();

        return $board
            ->recordTitleAttribute('candidate.user.name')
            ->cardSchema(fn (Schema $schema) => $schema
                ->components([
                    TextEntry::make('status')
                        ->label(__('applications::filament.fields.status'))
                        ->badge(),
                    TextEntry::make('candidate.total_work_experience_years'),
                    TextEntry::make('tracking_code'),
                ])
            )
            ->columnActions([
                Action::make('123')
                    ->label('fodase'),
                Action::make('1234')
                    ->label('fodase1'),
            ])
            ->cardActions([
                ViewCandidateAction::make()->model(Application::class),
                StateTransitionAction::make(),
            ])
            ->query(
                Application::query()
                    ->where('requisition_id', $jobRequisition->getKey())
            )
            ->columnIdentifier('current_stage_id')
            ->columns($columns);
    }
}
