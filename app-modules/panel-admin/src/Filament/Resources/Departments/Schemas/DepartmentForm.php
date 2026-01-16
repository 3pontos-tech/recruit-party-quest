<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->label(__('teams::filament.department.fields.team'))
                    ->relationship('team', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->live(),
                TextInput::make('name')
                    ->label(__('teams::filament.department.fields.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('head_user_id')
                    ->label(__('teams::filament.department.fields.head_user'))
                    ->relationship(
                        name: 'headUser',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, $get) => $query->when(
                            $get('team_id'),
                            fn ($q) => $q->whereHas(
                                'teams',
                                fn ($sq) => $sq->whereKey($get('team_id'))
                            )
                        ),
                    )
                    ->preload()
                    ->searchable(),
                Textarea::make('description')
                    ->label(__('teams::filament.department.fields.description'))
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }
}
