<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Stages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recruitment::filament.stage.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage_type')
                    ->label(__('recruitment::filament.stage.fields.stage_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label(__('recruitment::filament.stage.fields.display_order'))
                    ->sortable(),
                TextColumn::make('expected_duration_days')
                    ->label(__('recruitment::filament.stage.fields.expected_duration_days'))
                    ->sortable(),
                IconColumn::make('active')
                    ->label(__('recruitment::filament.stage.fields.active'))
                    ->boolean(),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                //                CreateAction::make()
                //                    ->mutateDataUsing(function (array $data): array {
                //                        /** @var Stage $model */
                //                        $model = $this->getOwnerRecord();
                //                        $data['team_id'] = $model->team_id;
                //
                //                        return $data;
                //                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
