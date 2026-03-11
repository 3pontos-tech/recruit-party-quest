<?php

declare(strict_types=1);

namespace He4rt\Links\Filament\Components;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
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
            Select::make('type')
                ->label('Platform')
                ->options(LinkTypeEnum::class)
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, ?LinkTypeEnum $state): void {
                    if (! $state instanceof LinkTypeEnum) {
                        return;
                    }

                    $set('name', $state->label());
                    $set('icon', $state->icon());
                }),

            TextInput::make('url')
                ->label('URL')
                ->required()
                ->url()
                ->placeholder(fn (Get $get): string => $get('type')?->urlPlaceholder() ?? 'https://')
                ->maxLength(2048),

            Hidden::make('name'),
            Hidden::make('icon'),
            Hidden::make('order_column'),
        ];
    }
}
