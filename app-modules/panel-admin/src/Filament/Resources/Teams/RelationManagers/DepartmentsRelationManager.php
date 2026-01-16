<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('teams::filament.relation_managers.departments.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('teams::filament.department.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('teams::filament.department.fields.description'))
                    ->limit(50),
                TextColumn::make('headUser.name')
                    ->label(__('teams::filament.department.fields.head_user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requisitions_count')
                    ->counts('requisitions')
                    ->label(__('teams::filament.department.fields.requisitions_count'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('teams::filament.department.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('teams::filament.department.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('teams::filament.department.plural_label');
    }
}
