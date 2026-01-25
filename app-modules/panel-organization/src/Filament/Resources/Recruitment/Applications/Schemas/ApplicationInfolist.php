<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                // Main Component - ViewComponent or w/e you want
                Section::make()
                    ->columnSpanFull()
                    ->schema([

                    ]),
                // Tabs for content check
                Tabs::make('fodase')
                    ->columnSpan(3)
                    ->schema([
                        Tab::make('vai brasil')
                            ->schema([
                                Select::make('team_id')
                                    ->relationship('team', 'name')
                                    ->label(__('teams::filament.department.fields.team'))
                                    ->live(),
                            ]),
                    ]),
                // Right Sidebar
                Grid::make()
                    ->columns(1)
                    ->schema([
                        Section::make()
                            ->schema([
                                Select::make('team_id')
                                    ->relationship('team', 'name')
                                    ->label(__('teams::filament.department.fields.team'))
                                    ->live(),
                            ]),
                        Section::make()
                            ->schema([
                                Select::make('team_id')
                                    ->relationship('team', 'name')
                                    ->label(__('teams::filament.department.fields.team'))
                                    ->live(),
                            ]),
                    ]),

            ]);
    }
}
