<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Stages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('recruitment::filament.stage.fields.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('stage_type')
                    ->label(__('recruitment::filament.stage.fields.stage_type'))
                    ->options(StageTypeEnum::class)
                    ->required(),
                TextInput::make('display_order')
                    ->label(__('recruitment::filament.stage.fields.display_order'))
                    ->numeric()
                    ->default(0)
                    ->required(),
                Textarea::make('description')
                    ->label(__('recruitment::filament.stage.fields.description'))
                    ->rows(3)
                    ->maxLength(1000),
                TextInput::make('expected_duration_days')
                    ->label(__('recruitment::filament.stage.fields.expected_duration_days'))
                    ->numeric()
                    ->minValue(1),
                Toggle::make('active')
                    ->label(__('recruitment::filament.stage.fields.active'))
                    ->default(true),
            ]);
    }
}
