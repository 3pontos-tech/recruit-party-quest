<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use Illuminate\Support\Str;

class JobRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('recruitment::filament.requisition.sections.basic_information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('recruitment::filament.requisition.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => (string) Str::uuid())
                            ->dehydrated()
                            ->columnSpanFull(),
                        Select::make('team_id')
                            ->label(__('recruitment::filament.requisition.fields.team'))
                            ->relationship('team', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->live(),
                        Select::make('department_id')
                            ->label(__('recruitment::filament.requisition.fields.department'))
                            ->relationship(
                                name: 'department',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, $get) => $query->when($get('team_id'), fn ($q) => $q->forTeam($get('team_id'))),
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('hiring_manager_id')
                            ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                            ->relationship(
                                name: 'hiringManager',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query, $get) => $query->when(
                                    $get('team_id'),
                                    fn ($q) => $q->whereHas(
                                        'teams',
                                        fn ($sq) => $sq->whereKey($get('team_id'))
                                    )
                                ),
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('status')
                            ->label(__('recruitment::filament.requisition.fields.status'))
                            ->options(RequisitionStatusEnum::class)
                            ->default(RequisitionStatusEnum::Draft)
                            ->required(),
                        Select::make('priority')
                            ->label(__('recruitment::filament.requisition.fields.priority'))
                            ->options(RequisitionPriorityEnum::class)
                            ->default(RequisitionPriorityEnum::Medium)
                            ->required(),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.position_details'))
                    ->columns(2)
                    ->schema([
                        Select::make('work_arrangement')
                            ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                            ->options(WorkArrangementEnum::class)
                            ->required(),
                        Select::make('employment_type')
                            ->label(__('recruitment::filament.requisition.fields.employment_type'))
                            ->options(EmploymentTypeEnum::class)
                            ->required(),
                        Select::make('experience_level')
                            ->label(__('recruitment::filament.requisition.fields.experience_level'))
                            ->options(ExperienceLevelEnum::class)
                            ->required(),
                        TextInput::make('positions_available')
                            ->label(__('recruitment::filament.requisition.fields.positions_available'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.compensation'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('salary_range_min')
                            ->label(__('recruitment::filament.requisition.fields.salary_range_min'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('salary_range_max')
                            ->label(__('recruitment::filament.requisition.fields.salary_range_max'))
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('salary_currency')
                            ->label(__('recruitment::filament.requisition.fields.salary_currency'))
                            ->default('USD')
                            ->maxLength(3),
                    ]),

                Section::make(__('recruitment::filament.requisition.sections.settings'))
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_internal_only')
                            ->label(__('recruitment::filament.requisition.fields.is_internal_only'))
                            ->default(false),
                        Toggle::make('is_confidential')
                            ->label(__('recruitment::filament.requisition.fields.is_confidential'))
                            ->default(false),
                        DateTimePicker::make('target_start_at')
                            ->label(__('recruitment::filament.requisition.fields.target_start_at')),
                    ]),
            ]);
    }
}
