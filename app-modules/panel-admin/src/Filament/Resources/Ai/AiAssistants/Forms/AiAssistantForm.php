<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiAssistants\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Enums\AiModel;
use Illuminate\Validation\Rule;

final class AiAssistantForm
{
    public function form(Schema|Component $form): Schema|Component
    {
        auth()->user();

        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('ai::filament.fields.avatar'))
                    ->disk('s3')
                    ->collection('avatar')
                    ->visibility('private')
                    ->avatar()
                    ->columnSpanFull()
                    ->acceptedFileTypes([
                        'image/png',
                        'image/jpeg',
                        'image/gif',
                    ]),
                TextInput::make('name')
                    ->required()
                    ->string()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('application')
                    ->options([
                        AiAssistantApplication::PersonalAssistant->value => __('ai::filament.enums.application.personal_assistant'),
                    ])
                    ->dehydratedWhenHidden()
                    ->default(AiAssistantApplication::getDefault())
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => filled(AiAssistantApplication::parse($state)) ? $set('model', AiAssistantApplication::parse($state)->getDefaultModel()->value) : null)
                    ->required()
                    ->enum(AiAssistantApplication::class)
                    ->columnStart(1)
                    ->visible(auth()->check())
                    ->disabledOn('edit'),
                Select::make('model')
                    ->reactive()
                    ->options(AiModel::class)
                    ->rule(Rule::enum(AiModel::class))
                    ->searchable()
                    ->required()
                    ->visible(fn (Get $get): bool => filled($get('application')) && auth()->check())
                    ->dehydratedWhenHidden(),
                Textarea::make('description')
                    ->columnSpanFull()
                    ->required(),
                Select::make('owner_id')
                    ->label(__('ai::filament.fields.owner'))
                    ->relationship('createdBy', 'name')
                    ->visible(auth()->check()),
                Section::make(__('ai::filament.sections.configure_ai_advisor.title'))
                    ->description(__('ai::filament.sections.configure_ai_advisor.description'))
                    ->schema([
                        Textarea::make('instructions')
                            ->reactive()
                            ->required()
                            ->maxLength(fn (Get $get): int => 500),
                    ]),

            ]);
    }
}
