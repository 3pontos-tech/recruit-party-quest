<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use App\Filament\Schemas\Components\He4rtInput;
use App\Filament\Schemas\Components\He4rtSelect;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use Illuminate\Database\Eloquent\Builder;
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
                                    ->description(__('recruitment::filament.requisition.sections.basic_information_description'))
                                    ->icon(Heroicon::Briefcase)
                                    ->relationship('post')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.title'))
                                            ->required()
                                            ->live(debounce: 700)
                                            ->afterStateUpdated(function (Set $set, string $state): void {
                                                $slug = sprintf('%s-%s', $state, Str::random(4));
                                                $set('slug', str($slug)->slug());
                                            })
                                            ->columnSpanFull(),
                                        TextInput::make('slug')
                                            ->readOnly()
                                            ->prefix('/vagas/')
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.slug'))
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('summary')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.summary'))
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->required()
                                            ->label('descrição da vaga')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.position_details'))
                                    ->description(__('recruitment::filament.requisition.sections.position_details_description'))
                                    ->icon(Heroicon::Briefcase)
                                    ->schema([
                                        He4rtSelect::make('department_id')
                                            ->label(__('recruitment::filament.requisition.fields.department'))
                                            ->relationship(
                                                name: 'department',
                                                titleAttribute: 'name',
                                                /** @phpstan-ignore-next-line */
                                                modifyQueryUsing: fn (
                                                    Builder $query,
                                                    Get $get
                                                ) => $query->when($get('team_id'),
                                                    fn (Builder $q) => $q->forTeam($get('team_id'))),
                                            )
                                            ->description(__('recruitment::filament.requisition.fields.department_description'))
                                            ->icon(Heroicon::BuildingOffice)
                                            ->required()
                                            ->iconColor('purple')
                                            ->preload()
                                            ->searchable(),
                                        He4rtSelect::make('work_arrangement')
                                            ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                                            ->options(WorkArrangementEnum::class)
                                            ->icon(Heroicon::Home)
                                            ->iconColor('red')
                                            ->description('Where and how the employee will work')
                                            ->native(false)
                                            ->required(),
                                        He4rtSelect::make('employment_type')
                                            ->label(__('recruitment::filament.requisition.fields.employment_type'))
                                            ->options(EmploymentTypeEnum::class)
                                            ->description(__('recruitment::filament.requisition.fields.employment_type_description'))
                                            ->icon(Heroicon::Clock)
                                            ->native(false)
                                            ->iconColor('green')
                                            ->required(),
                                        He4rtSelect::make('experience_level')
                                            ->label(__('recruitment::filament.requisition.fields.experience_level'))
                                            ->options(ExperienceLevelEnum::class)
                                            ->iconColor('yellow')
                                            ->description('Required seniority for this position')
                                            ->icon(Heroicon::CheckBadge)
                                            ->native(false)
                                            ->required(),

                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.team_ownership'))
                                    ->icon(Heroicon::Cog)
                                    ->schema([
                                        He4rtInput::make('positions_available')
                                            ->label(__('recruitment::filament.requisition.fields.positions_available'))
                                            ->numeric()
                                            ->icon(Heroicon::Users)
                                            ->iconColor('blue')
                                            ->description(__('recruitment::filament.requisition.fields.positions_available_description'))
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),
                                        He4rtSelect::make('recruiter_id')
                                            ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                                            ->relationship(
                                                name: 'recruiter',
                                                /** @phpstan-ignore-next-line */
                                                modifyQueryUsing: fn (
                                                    Builder $query,
                                                    Get $get
                                                ) => $query->when($get('team_id'),
                                                    fn (Builder $q) => $q->forTeam($get('team_id'))),
                                            )
                                            ->getOptionLabelFromRecordUsing(fn (Recruiter $record
                                            ) => $record->user->name)
                                            ->icon(Heroicon::Users)
                                            ->iconColor('red')
                                            ->description(__('recruitment::filament.requisition.fields.hiring_manager_description'))
                                            ->required()
                                            ->preload()
                                            ->searchable(),
                                        He4rtSelect::make('status')
                                            ->label(__('recruitment::filament.requisition.fields.status'))
                                            ->icon(Heroicon::Squares2x2)
                                            ->iconColor('gray')
                                            ->description('Draft? Reviewing? Published?')
                                            ->options(RequisitionStatusEnum::class)
                                            ->default(RequisitionStatusEnum::Draft)
                                            ->required(),
                                        He4rtSelect::make('priority')
                                            ->label(__('recruitment::filament.requisition.fields.priority'))
                                            ->description('How fast we must close this position?')
                                            ->icon(Heroicon::Cube)
                                            ->iconColor('yellow')
                                            ->options(RequisitionPriorityEnum::class)
                                            ->default(RequisitionPriorityEnum::Medium)
                                            ->required(),
                                    ]),
                            ]),

                        Tab::make(__('recruitment::filament.requisition.tabs.job_description'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('recruitment::filament.requisition.sections.job_description'))
                                    ->description(__('recruitment::filament.requisition.sections.section_description'))
                                    ->schema([
                                        self::makeItemsRepeater('items', JobRequisitionItemTypeEnum::Description),
                                    ]),
                            ]),
                        Tab::make(__('recruitment::filament.requisition.tabs.requirements'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('recruitment::filament.requisition.sections.required_qualifications'))
                                    ->description(__('recruitment::filament.requisition.sections.required_qualifications_description'))
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'requiredQualifications',
                                            JobRequisitionItemTypeEnum::RequiredQualification,
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.preferred_qualifications'))
                                    ->description(__('recruitment::filament.requisition.sections.preferred_qualifications_description'))
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'preferredQualifications',
                                            JobRequisitionItemTypeEnum::PreferredQualification,
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.responsibilities'))
                                    ->description(__('recruitment::filament.requisition.sections.responsabilities_description'))
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'responsibilities',
                                            JobRequisitionItemTypeEnum::Responsibility,
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.benefits'))
                                    ->description(__('recruitment::filament.requisition.sections.benefits_description'))
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'benefits',
                                            JobRequisitionItemTypeEnum::Benefit,
                                        ),
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
                                        Select::make('salary_currency')
                                            ->label(__('recruitment::filament.requisition.fields.salary_currency'))
                                            ->default('BRL')
                                            ->options([
                                                'USD' => 'USD',
                                                'EUR' => 'EUR',
                                                'GBP' => 'GBP',
                                                'BRL' => 'BRL',
                                            ]),
                                        Toggle::make('show_salary_to_candidates')
                                            ->default(false)
                                            ->label(__('recruitment::filament.requisition.fields.show_salary_to_candidates')),
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
                                Section::make(__('recruitment::filament.requisition.sections.external_posting'))
                                    ->relationship('post')
                                    ->schema([
                                        Hidden::make('team_id')
                                            ->default(filament()->getTenant()?->getKey()),
                                        TextInput::make('external_post_url')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.external_post_url'))
                                            ->url()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function makeItemsRepeater(
        string $relationshipName,
        JobRequisitionItemTypeEnum $type,
    ): Repeater {
        return Repeater::make($relationshipName)
            ->hiddenLabel()
            ->relationship(
                name: 'items',
                modifyQueryUsing: fn ($query) => $query->where('type', $type),
            )
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data) use ($type): array {
                $data['type'] = $type;

                return $data;
            })
            ->orderColumn('order')
            ->reorderable()
            ->collapsible()
            ->cloneable()
            ->itemLabel(fn (array $state): ?string => $state['content'] ?? null)
            ->schema([
                TextInput::make('content')
                    ->label(__('recruitment::filament.requisition.job_posting.fields.content'))
                    ->required()
                    ->columnSpanFull(),
                SpatieTagsInput::make('tags')
                    ->label(__('recruitment::filament.requisition.job_posting.fields.tags'))
                    ->columnSpanFull(),
            ]);
    }
}
