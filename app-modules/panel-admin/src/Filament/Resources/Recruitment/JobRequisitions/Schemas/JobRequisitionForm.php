<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Screening\Filament\Schemas\ScreeningQuestionsFormSchema;
use Illuminate\Database\Eloquent\Builder;

class JobRequisitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('requisition_tabs')
                    ->tabs([
                        Tab::make(__('recruitment::filament.requisition.tabs.details'))
                            ->icon('heroicon-o-document-text')
                            ->schema(self::detailsTabSchema()),

                        Tab::make(__('recruitment::filament.requisition.tabs.screening_questions'))
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                ScreeningQuestionsFormSchema::make(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    private static function detailsTabSchema(): array
    {
        return [
            Section::make(__('recruitment::filament.requisition.sections.basic_information'))
                ->columns(2)
                ->schema([
                    Select::make('team_id')
                        ->label(__('recruitment::filament.requisition.fields.team'))
                        ->relationship('team', 'name')
                        ->required()
                        ->preload()
                        ->searchable()
                        ->afterStateUpdated(function (string $state, Set $set, Get $get): void {
                            $questions = $get('screeningQuestions') ?? [];

                            foreach ($questions as $key => $question) {
                                $set(sprintf('screeningQuestions.%s.team_id', $key), $state);
                            }
                        })
                        ->live(),
                    Select::make('department_id')
                        ->label(__('recruitment::filament.requisition.fields.department'))
                        ->relationship(
                            name: 'department',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query, $get) => $query->when($get('team_id'),
                                fn ($q) => $q->forTeam($get('team_id'))),
                        )
                        ->required()
                        ->preload()
                        ->searchable(),
                    Select::make('recruiter_id')
                        ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                        ->relationship(
                            name: 'recruiter',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn ($query, $get) => $query->when(
                                $get('team_id'),
                                fn (Builder $q) => $q->whereHas(
                                    'team',
                                    fn (\Illuminate\Contracts\Database\Query\Builder $sq
                                    ) => $sq->whereKey($get('team_id'))
                                )
                            ),
                        )
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name)
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
                    Toggle::make('show_salary_to_candidates')
                        ->label(__('recruitment::filament.requisition.fields.show_salary_to_candidates'))
                        ->default(false),
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
        ];
    }
}
