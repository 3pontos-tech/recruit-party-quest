<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\Tables;

use App\Enums\FilamentPanel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use He4rt\Teams\Team;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('teams::filament.fields.id')),
                TextColumn::make('name')
                    ->label(__('teams::filament.fields.name'))
                    ->description(fn ($record): string => __('teams::filament.table.slug_description', ['slug' => $record->slug]))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('teams::filament.fields.status'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('teams::filament.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('members_count')
                    ->counts('members')
                    ->badge()
                    ->label(__('teams::filament.fields.members_count')),
                TextColumn::make('updated_at')
                    ->label(__('teams::filament.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('teams::filament.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('manage')
                    ->label(__('teams::filament.actions.manage'))
                    ->icon(Heroicon::ArrowTopRightOnSquare)
                    ->url(fn (Team $record): string => Dashboard::getUrl(panel: FilamentPanel::Organization->value, tenant: $record)),
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
