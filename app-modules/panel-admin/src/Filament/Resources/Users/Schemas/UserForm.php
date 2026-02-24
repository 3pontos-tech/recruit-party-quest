<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Builder;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('users::labels.name'))
                    ->required(),

                TextInput::make('email')
                    ->label(__('users::labels.email'))
                    ->required(),

                DatePicker::make('email_verified_at')
                    ->label(__('users::labels.email_verified_at')),

                TextInput::make('password')
                    ->label(__('users::labels.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create'),

                Select::make('ownedTeam')
                    ->label(__('users::labels.owned_team'))
                    ->relationship(
                        name: 'ownedTeam',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?User $record) => $query->where(
                            function (Builder $q) use ($record): void {
                                $q->whereNull('owner_id');
                                if ($record?->getKey()) {
                                    $q->orWhere('owner_id', $record->getKey());
                                }
                            }
                        ),
                    )
                    ->nullable()
                    ->searchable(),
            ]);
    }
}
