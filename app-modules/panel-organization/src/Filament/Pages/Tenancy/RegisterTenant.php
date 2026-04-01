<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Pages\Tenancy;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant as BaseRegisterTenant;
use Filament\Schemas\Schema;
use He4rt\Teams\Team;
use He4rt\Teams\TeamStatus;

class RegisterTenant extends BaseRegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('teams::filament.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label(__('teams::filament.fields.description'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label(__('teams::filament.fields.slug'))
                    ->required()
                    ->maxLength(255)
                    ->unique(Team::class, 'slug'),
                TextInput::make('contact_email')
                    ->label(__('teams::filament.fields.contact_email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label(__('teams::filament.fields.status'))
                    ->options(TeamStatus::class)
                    ->hidden()
                    ->default(TeamStatus::Active),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $data['owner_id'] = auth()->id();
        $data['status'] = TeamStatus::Active;

        $team = Team::query()->create($data);

        $team->members()->attach(auth()->user());

        return $team;
    }
}
