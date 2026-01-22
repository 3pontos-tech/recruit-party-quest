<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('applications::filament.fields.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('tracking_code')
                    ->label(__('applications::filament.fields.tracking_code'))
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('candidate.user.name')
                    ->label(__('applications::filament.fields.candidate'))
                    ->searchable()
                    ->description(fn (Application $record) => $record->requisition->post?->title ?? 'N/A')
                    ->sortable(),
                TextColumn::make('requisition.id')
                    ->label(__('applications::filament.fields.requisition'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('status')
                    ->label(__('applications::filament.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('source')
                    ->label(__('applications::filament.fields.source'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('currentStage.name')
                    ->label(__('applications::filament.fields.current_stage'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('evaluations_count')
                    ->label(__('applications::filament.fields.evaluations_count'))
                    ->counts('evaluations')
                    ->badge()
                    ->sortable(),
                TextColumn::make('offer_amount')
                    ->label(__('applications::filament.fields.offer_amount'))
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('applications::filament.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rejected_at')
                    ->label(__('applications::filament.fields.rejected_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('applications::filament.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('applications::filament.filters.status'))
                    ->options(ApplicationStatusEnum::class),
                SelectFilter::make('source')
                    ->label(__('applications::filament.filters.source'))
                    ->options(CandidateSourceEnum::class),
                SelectFilter::make('requisition')
                    ->label(__('applications::filament.filters.requisition'))
                    ->relationship('requisition', 'id')
                    ->preload(),
                SelectFilter::make('current_stage')
                    ->label(__('applications::filament.filters.current_stage'))
                    ->relationship('currentStage', 'name')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
