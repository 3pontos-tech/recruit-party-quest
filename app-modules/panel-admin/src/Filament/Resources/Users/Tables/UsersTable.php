<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('users::labels.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('users::labels.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label(__('users::labels.email_verified_at'))
                    ->date(),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
