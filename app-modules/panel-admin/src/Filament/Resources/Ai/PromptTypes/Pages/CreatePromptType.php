<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;

final class CreatePromptType extends CreateRecord
{
    protected static string $resource = PromptTypeResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns()
                    ->schema([
                        TextInput::make('title')
                            ->unique()
                            ->required()
                            ->string()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->string()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
