<?php

declare(strict_types=1);

namespace He4rt\Links\Filament\Components;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use He4rt\Links\LinkTypeEnum;

class LinkRepeater
{
    public static function make(
        string $name = 'links',
        ?string $label = 'Links',
        bool $useRelationship = true
    ): Repeater {
        $repeater = Repeater::make($name)
            ->label($label)
            ->orderColumn('order_column')
            ->defaultItems(0)
            ->collapsible()
            ->cloneable()
            ->schema(static::schema());

        if ($useRelationship) {
            $repeater->relationship($name);
        }

        return $repeater;
    }

    /**
     * @return array<int, Component>
     */
    protected static function schema(): array
    {
        return [
            TextInput::make('url')
                ->label('URL')
                ->required()
                ->url()
                ->maxLength(2048),

            Group::make()->schema([
                TextInput::make('name')
                    ->label('Label')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Type')
                    ->options(LinkTypeEnum::class)
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('icon', null))
                    ->nullable(),
            ])->columns(),

            He4rtIconPicker::make('icon')
                ->label('Icon')
                ->listSearchResults()
                ->placeholder('heroicon-o-link')
                ->visible(fn (Get $get): bool => $get('type') instanceof LinkTypeEnum)
                ->allowedIcons(function (Get $get): array {
                    $value = $get('type');
                    $type = $value instanceof LinkTypeEnum ? $value : LinkTypeEnum::tryFrom($value ?? '');

                    return $type?->icons() ?? LinkTypeEnum::allIcons();
                })
                ->extraAttributes(fn (Get $get): array => [
                    'wire:key' => 'icon-picker-'.($get('type') instanceof LinkTypeEnum ? $get('type')->value : ($get('type') ?? 'all')),
                ])
                ->nullable(),

            Hidden::make('order_column'),
        ];
    }
}
