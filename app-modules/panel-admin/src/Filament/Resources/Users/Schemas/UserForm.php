<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Users\Schemas;

use App\Filament\Shared\Fields\EmailTextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use He4rt\Permissions\Roles;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('users::labels.name'))
                    ->required(),

                EmailTextInput::make('email')
                    ->label(__('users::labels.email'))
                    ->required(),

                DatePicker::make('email_verified_at')
                    ->label(__('users::labels.email_verified_at')),

                TextInput::make('password')
                    ->label(__('users::labels.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create'),

                Select::make('roles')
                    ->multiple()
                    ->relationship('roles', 'name', fn ($query) => $query->where('name', Roles::Admin->value)),
            ]);
    }
}
