<?php

declare(strict_types=1);

namespace He4rt\Links\Filament\Components;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Guava\IconPicker\Forms\Components\IconPicker;
use He4rt\Links\LinkTypeEnum;

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
            TextInput::make('url.en')
                ->label('URL')
                ->required()
                ->url()
                ->maxLength(2048),

            Group::make()->schema([
                TextInput::make('name.en')
                    ->label('Label')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Type')
                    ->options(LinkTypeEnum::class)
                    ->nullable(),
            ])->columns(),

            IconPicker::make('icon')
                ->label('Icon')
                ->listSearchResults()
                ->placeholder('heroicon-o-link')
                ->nullable(),

            Hidden::make('order_column'),
        ];
    }
}
