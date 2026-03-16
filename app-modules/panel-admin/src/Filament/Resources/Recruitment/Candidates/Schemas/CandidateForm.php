<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Candidates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('candidates::filament.sections.user_info'))
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('candidates::filament.fields.user'))
                                    ->relationship(
                                        name: 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query, $livewire) => $query->whereDoesntHave('candidate', function ($q) use ($livewire): void {
                                            if (isset($livewire->record)) {
                                                $q->where('id', '!=', $livewire->record->id);
                                            }
                                        }),
                                    )
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('email')
                                            ->email()
                                            ->required(),
                                        TextInput::make('password')
                                            ->password()
                                            ->required(),
                                    ]),
                                TextInput::make('phone_number')
                                    ->label(__('candidates::filament.fields.phone'))
                                    ->tel()
                                    ->mask(RawJs::make(<<<'JS'
                                      '(99)99999-9999'
                                    JS))
                                    ->prefixIcon(Heroicon::Phone)
                                    ->maxLength(20),
                            ])->aside(),

                        Section::make(__('candidates::filament.sections.professional_info'))
                            ->schema([
                                TextInput::make('headline')
                                    ->label(__('candidates::filament.fields.headline'))
                                    ->maxLength(255),
                                Textarea::make('summary')
                                    ->label(__('candidates::filament.fields.summary'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->aside(),
                    ]),

                Section::make(__('candidates::filament.sections.availability'))
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('availability_date')
                            ->label(__('candidates::filament.fields.availability_date'))
                            ->prefixIcon(Heroicon::Calendar)
                            ->native(false)
                            ->placeholder('dd-mm-yyyy')
                            ->displayFormat('d/m/Y'),
                        Fieldset::make('preferences')
                            ->label(__('candidates::filament.fields.work_preferences'))
                            ->schema([
                                Toggle::make('willing_to_relocate')
                                    ->label(__('candidates::filament.fields.is_willing_to_relocate'))
                                    ->default(false),
                                Toggle::make('is_open_to_remote')
                                    ->label(__('candidates::filament.fields.is_open_to_remote'))
                                    ->default(true),
                            ]),
                    ])->aside(),

                Section::make(__('candidates::filament.sections.compensation'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('expected_salary')
                            ->label(__('candidates::filament.fields.expected_salary'))
                            ->mask(RawJs::make('$money($input, ",", ".")'))
                            ->dehydrateStateUsing(fn ($state) => str_replace(['.', ','], ['', '.'], $state)
                            )
                            ->prefix('$')
                            ->placeholder('00.000,00'),
                        TextInput::make('expected_salary_currency')
                            ->label(__('candidates::filament.fields.expected_salary_currency'))
                            ->default('USD')
                            ->maxLength(3),
                    ])->aside(),

            ]);
    }
}
