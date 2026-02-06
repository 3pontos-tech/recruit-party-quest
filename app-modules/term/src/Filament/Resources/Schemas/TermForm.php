<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Resources\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('term::filament.sections.general'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('term::filament.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, $record): void {
                                if ($record !== null || $state === null) {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->label(__('term::filament.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'terms', column: 'slug', ignoreRecord: true)
                            ->alphaDash(),
                        Toggle::make('is_active')
                            ->label(__('term::filament.fields.is_active'))
                            ->default(true),
                    ]),

                Section::make(__('term::filament.sections.sections'))
                    ->schema([
                        Repeater::make('content')
                            ->label(__('term::filament.fields.content'))
                            ->collapsible()
                            ->cloneable()
                            ->reorderable()
                            ->itemLabel(fn (array $state): string => $state['title'] ?? __('term::filament.fields.new_section'))
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('term::filament.fields.section_title'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (?string $state, callable $set): void {
                                        if ($state === null) {
                                            return;
                                        }

                                        $set('id', Str::slug($state));
                                    }),
                                TextInput::make('id')
                                    ->label(__('term::filament.fields.section_id'))
                                    ->required()
                                    ->alphaDash(),
                                Toggle::make('show_in_sidebar')
                                    ->label(__('term::filament.fields.show_in_sidebar'))
                                    ->default(true),
                                RichEditor::make('body')
                                    ->label(__('term::filament.fields.body'))
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
