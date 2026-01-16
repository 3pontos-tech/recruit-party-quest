<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\PromptTypes\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\PromptTypes\PromptTypeResource;

final class EditPromptType extends EditRecord
{
    // use EditPageRedirection;

    protected static string $resource = PromptTypeResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns()
                    ->schema([
                        TextInput::make('title')
                            ->unique(ignoreRecord: true)
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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
