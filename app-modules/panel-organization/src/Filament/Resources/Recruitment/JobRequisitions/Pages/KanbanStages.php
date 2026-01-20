<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;
use Relaticle\Flowforge\Concerns\InteractsWithBoard;

class KanbanStages extends BoardResourcePage
{
    use InteractsWithBoard;

    protected static string $resource = JobRequisitionResource::class;

    public function board(Board $board): Board
    {
        $columns = collect(RequisitionStatusEnum::cases())
            ->map(fn (RequisitionStatusEnum $status) => Column::make($status->value)->label($status->getLabel())->color($status->getColor()))
            ->toArray();

        return $board
            ->recordTitleAttribute('post.title')
            ->cardSchema(fn (Schema $schema) => $schema
                ->components([
                    TextEntry::make('priority'),
                    TextEntry::make('work_arrangement'),
                    TextEntry::make('employment_type'),
                    TextEntry::make('experience_level'),
                ])
            )
            ->cardActions([
                EditAction::make()->model(JobRequisition::class),
            ])
            ->query(JobRequisition::query()->with('stages'))
            ->columnIdentifier('status')
            ->positionIdentifier('display_order')
            ->columns($columns);
    }
}
