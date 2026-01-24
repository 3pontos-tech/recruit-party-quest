<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Filament\Schema;

use Illuminate\Database\Eloquent\Model;
use Override;
use Relaticle\Flowforge\Board;

class KanbanBoard extends Board
{
    #[Override]
    public function getViewData(): array
    {
        // Batch all column counts in a single query
        $allCounts = $this->getBatchedBoardRecordCounts();

        // Build columns data using new concerns
        $columns = [];
        /** @var KanbanColumn $column */
        foreach ($this->getColumns() as $column) {
            $columnId = $column->getName();

            // Get formatted records
            $records = $this->getBoardRecords($columnId);
            $formattedRecords = $records->map(fn (Model $record) => $this->formatBoardRecord($record))->all();

            $columns[$columnId] = [
                'id' => $columnId,
                'label' => $column->getLabel(),
                'color' => $column->getColor(),
                'icon' => $column->getIcon(),
                'description' => $column->getDescription(),
                'recruiters' => $column->getRecruiters(),
                'items' => $formattedRecords,
                'total' => $allCounts[$columnId] ?? 0,
            ];
        }

        return [
            'columns' => $columns,
            'config' => [
                'recordTitleAttribute' => $this->getRecordTitleAttribute(),
                'columnIdentifierAttribute' => $this->getColumnIdentifierAttribute(),
                'cardLabel' => __('flowforge::flowforge.card_label'),
                'pluralCardLabel' => __('flowforge::flowforge.plural_card_label'),
            ],
        ];
    }
}
