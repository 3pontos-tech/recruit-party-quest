<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\Applications\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Applications\Models\Application;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('candidate_id', auth()->user()->candidate->getKey()))
            ->columns([
                TextColumn::make('tracking_code'),

                TextColumn::make('requisition.post.title')
                    ->description(fn (Application $record) => $record->team->name)
                    ->searchable(),
                TextColumn::make('currentStage.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
