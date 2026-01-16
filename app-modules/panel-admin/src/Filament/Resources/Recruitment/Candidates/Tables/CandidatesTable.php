<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Candidates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('candidates::filament.fields.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('candidates::filament.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label(__('candidates::filament.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('candidates::filament.fields.phone'))
                    ->searchable(),
                TextColumn::make('headline')
                    ->label(__('candidates::filament.fields.headline'))
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('availability_date')
                    ->label(__('candidates::filament.fields.availability_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expected_salary')
                    ->label(__('candidates::filament.fields.expected_salary'))
                    ->money(fn ($record): string => $record->expected_salary_currency ?? 'USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('skills_count')
                    ->label(__('candidates::filament.fields.skills_count'))
                    ->counts('skills')
                    ->badge()
                    ->sortable(),
                TextColumn::make('applications_count')
                    ->label(__('candidates::filament.fields.applications_count'))
                    ->counts('applications')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('candidates::filament.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('candidates::filament.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('candidates::filament.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('willing_to_relocate')
                    ->label(__('candidates::filament.filters.is_willing_to_relocate')),
                TernaryFilter::make('is_open_to_remote')
                    ->label(__('candidates::filament.filters.is_open_to_remote')),
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
