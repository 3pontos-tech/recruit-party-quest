<?php

declare(strict_types=1);

namespace He4rt\Links\Filament\Components;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Guava\IconPicker\Forms\Components\IconPicker;

class LinkRepeater
{
    public static function make(
        string $relationship = 'links',
        ?string $label = 'Links'
    ): Repeater {
        return Repeater::make($relationship)
            ->label($label)
            ->relationship($relationship)
            ->orderColumn('order_column')
            ->defaultItems(0)
            ->collapsible()
            ->cloneable()
            ->schema(static::schema());
    }

    protected static function schema(): array
    {
        return [
            TextInput::make('name.en')
                ->label('Label')
                ->required()
                ->maxLength(255),

            TextInput::make('slug.en')
                ->label('Slug')
                ->required()
                ->maxLength(255),

            TextInput::make('url.en')
                ->label('URL')
                ->required()
                ->url()
                ->maxLength(2048),

            Select::make('type')
                ->label('Type')
                ->options([
                    'primary' => 'Primary',
                    'secondary' => 'Secondary',
                    'external' => 'External',
                ])
                ->nullable(),

            IconPicker::make('icon')
                ->label('Icon')
                ->listSearchResults()
                ->placeholder('heroicon-o-link')
                ->nullable(),

            Hidden::make('order_column'),
        ];
    }
}
