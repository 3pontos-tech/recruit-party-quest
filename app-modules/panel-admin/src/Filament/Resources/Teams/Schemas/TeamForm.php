<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use He4rt\Teams\TeamStatus;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label(__('teams::filament.fields.owner'))
                    ->relationship('owner', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label(__('teams::filament.fields.name'))
                    ->required(),
                TextInput::make('description')
                    ->label(__('teams::filament.fields.description'))
                    ->required(),
                TextInput::make('slug')
                    ->label(__('teams::filament.fields.slug'))
                    ->required(),
                Select::make('status')
                    ->label(__('teams::filament.fields.status'))
                    ->options(TeamStatus::class)
                    ->required(),
                TextInput::make('contact_email')
                    ->label(__('teams::filament.fields.contact_email'))
                    ->email()
                    ->required(),
            ]);
    }
}
