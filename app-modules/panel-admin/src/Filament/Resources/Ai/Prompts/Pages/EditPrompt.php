<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\Prompts\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\Prompts\PromptResource;

final class EditPrompt extends EditRecord
{
    // use EditPageRedirection;

    protected static string $resource = PromptResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->string()
                            ->maxLength(255),
                        Select::make('type_id')
                            ->relationship('type', 'title')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Textarea::make('description')
                            ->string()
                            ->columnSpanFull(),
                        MarkdownEditor::make('prompt')
                            ->required()
                            ->string()
                            ->columnSpanFull(),
                        ToggleButtons::make('is_smart')
                            ->label('Kind')
                            ->options([
                                0 => 'Custom',
                                1 => 'Smart',
                            ])
                            ->live()
                            ->grouped()
                            ->visible(true),

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
