<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('candidate::filament.sections.user_info'))
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label(__('candidate::filament.fields.user'))
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
                            ->label(__('candidate::filament.fields.phone'))
                            ->tel()
                            ->maxLength(20),
                    ]),

                Section::make(__('candidate::filament.sections.professional_info'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('headline')
                            ->label(__('candidate::filament.fields.headline'))
                            ->maxLength(255),
                        Textarea::make('summary')
                            ->label(__('candidate::filament.fields.summary'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('candidate::filament.sections.availability'))
                    ->columns(3)
                    ->schema([
                        DatePicker::make('availability_date')
                            ->label(__('candidate::filament.fields.availability_date')),
                        Toggle::make('willing_to_relocate')
                            ->label(__('candidate::filament.fields.is_willing_to_relocate'))
                            ->default(false),
                        Toggle::make('is_open_to_remote')
                            ->label(__('candidate::filament.fields.is_open_to_remote'))
                            ->default(true),
                    ]),

                Section::make(__('candidate::filament.sections.compensation'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('expected_salary')
                            ->label(__('candidate::filament.fields.expected_salary'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('expected_salary_currency')
                            ->label(__('candidate::filament.fields.expected_salary_currency'))
                            ->default('USD')
                            ->maxLength(3),
                    ]),

                Section::make(__('candidate::filament.sections.links'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('linkedin_url')
                            ->label(__('candidate::filament.fields.linkedin_url'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('portfolio_url')
                            ->label(__('candidate::filament.fields.portfolio_url'))
                            ->url()
                            ->maxLength(255),
                    ]),
            ]);
    }
}
