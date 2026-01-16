<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use He4rt\Ai\Models\AiThread;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ThreadsRelationManager extends RelationManager
{
    protected static string $relationship = 'threads';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('thread_id'),

                Select::make('assistant_id')
                    ->relationship('assistant', 'name')
                    ->searchable()
                    ->required(),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('name'),

                TextInput::make('cloned_count')
                    ->required()
                    ->integer(),

                TextInput::make('emailed_count')
                    ->required()
                    ->integer(),

                DatePicker::make('saved_at')
                    ->label('Saved Date'),

                DatePicker::make('locked_at')
                    ->label('Locked Date'),

                TextEntry::make('created_at')
                    ->label('Created Date')
                    ->state(fn (?AiThread $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                TextEntry::make('updated_at')
                    ->label('Last Modified Date')
                    ->state(fn (?AiThread $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                Checkbox::make('registerMediaConversionsUsingModelInstance'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('thread_id'),

                TextColumn::make('assistant.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cloned_count'),

                TextColumn::make('emailed_count'),

                TextColumn::make('saved_at')
                    ->label('Saved Date')
                    ->date(),

                TextColumn::make('locked_at')
                    ->label('Locked Date')
                    ->date(),

                TextColumn::make('registerMediaConversionsUsingModelInstance'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
