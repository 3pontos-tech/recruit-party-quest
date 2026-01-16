<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use He4rt\Permissions\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('permissions::filament.fields.name'))
                                    ->unique(
                                        ignoreRecord: true,
                                    )
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('guard_name')
                                    ->label(__('permissions::filament.fields.guard_name'))
                                    ->default('web')
                                    ->nullable()
                                    ->maxLength(255),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                self::getPermissionsFormComponent(),
            ]);
    }

    public static function getPermissionsFormComponent(): Component
    {
        return Tabs::make(__('permissions::filament.tabs.permissions'))
            ->tabs([
                Tab::make(__('permissions::filament.tabs.permissions'))
                    ->schema([
                        PermissionsCheckboxList::make('permissions')
                            ->hiddenLabel()
                            ->getOptionLabelFromRecordUsing(
                                fn (Permission $record) => $record->formatted_name
                            )
                            ->relationship('permissions', 'name')
                            ->bulkToggleable(),
                    ]),
            ])
            ->columnSpanFull();
    }
}
