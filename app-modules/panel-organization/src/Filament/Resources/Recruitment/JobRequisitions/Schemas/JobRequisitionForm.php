<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
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
                Tabs::make('Job Requisition Tabs')
                    ->columnSpanFull()
                    ->vertical()
                    ->tabs([
                        Tab::make(__('recruitment::filament.requisition.tabs.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
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
                                                modifyQueryUsing: fn ($query, Get $get) => $query->when($get('team_id'), fn ($q) => $q->forTeam($get('team_id'))),
                                            )
                                            ->required()
                                            ->preload()
                                            ->searchable(),
                                        Select::make('recruiter_id')
                                            ->label(__('recruitment::filament.requisition.fields.recruiter'))
                                            ->relationship(
                                                name: 'recruiter',
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
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
                            ]),

                        Tab::make(__('recruitment::filament.requisition.tabs.position_details'))
                            ->icon('heroicon-o-briefcase')
                            ->schema([
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
                            ]),

                        Tab::make(__('recruitment::filament.requisition.tabs.compensation'))
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
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
                            ]),

                        Tab::make(__('recruitment::filament.requisition.tabs.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
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
                                            ->label(__('recruitment::filament.requisition.fields.target_start_at'))
                                            ->minDate(now()),
                                    ]),
                            ]),

                        Tab::make(__('recruitment::filament.requisition.tabs.job_posting'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('recruitment::filament.requisition.sections.job_posting_details'))
                                    ->relationship('post')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('recruitment::filament.job_posting.fields.title'))
                                            ->required()
                                            ->columnSpanFull(),
                                        TextInput::make('slug')
                                            ->label(__('recruitment::filament.job_posting.fields.slug'))
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('summary')
                                            ->label(__('recruitment::filament.job_posting.fields.summary'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('description')
                                            ->label(__('recruitment::filament.job_posting.fields.description'))
                                            ->rows(6)
                                            ->columnSpanFull(),
                                        Textarea::make('responsibilities')
                                            ->label(__('recruitment::filament.job_posting.fields.responsibilities'))
                                            ->helperText(__('recruitment::filament.job_posting.helpers.one_per_line'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        Textarea::make('required_qualifications')
                                            ->label(__('recruitment::filament.job_posting.fields.required_qualifications'))
                                            ->helperText(__('recruitment::filament.job_posting.helpers.one_per_line'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        Textarea::make('preferred_qualifications')
                                            ->label(__('recruitment::filament.job_posting.fields.preferred_qualifications'))
                                            ->helperText(__('recruitment::filament.job_posting.helpers.one_per_line'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        Textarea::make('benefits')
                                            ->label(__('recruitment::filament.job_posting.fields.benefits'))
                                            ->helperText(__('recruitment::filament.job_posting.helpers.one_per_line'))
                                            ->rows(5)
                                            ->columnSpanFull(),
                                        Textarea::make('about_company')
                                            ->label(__('recruitment::filament.job_posting.fields.about_company'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('about_team')
                                            ->label(__('recruitment::filament.job_posting.fields.about_team'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('work_schedule')
                                            ->label(__('recruitment::filament.job_posting.fields.work_schedule'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('accessibility_accommodations')
                                            ->label(__('recruitment::filament.job_posting.fields.accessibility_accommodations'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Toggle::make('is_disability_confident')
                                            ->label(__('recruitment::filament.job_posting.fields.is_disability_confident'))
                                            ->default(false),
                                        TextInput::make('external_post_url')
                                            ->label(__('recruitment::filament.job_posting.fields.external_post_url'))
                                            ->url()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
