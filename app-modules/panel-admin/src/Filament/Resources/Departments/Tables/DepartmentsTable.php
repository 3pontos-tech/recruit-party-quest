<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('teams::filament.department.fields.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('team.name')
                    ->label(__('teams::filament.department.fields.team'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('teams::filament.department.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('headUser.name')
                    ->label(__('teams::filament.department.fields.head_user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requisitions_count')
                    ->label(__('teams::filament.department.fields.requisitions_count'))
                    ->counts('requisitions')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('teams::filament.department.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('teams::filament.department.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('teams::filament.department.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('team')
                    ->relationship('team', 'name')
                    ->label(__('teams::filament.department.filters.team'))
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
