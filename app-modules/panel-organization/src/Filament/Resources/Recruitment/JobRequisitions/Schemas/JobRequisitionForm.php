<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas;

use App\Filament\Schemas\Components\He4rtInput;
use App\Filament\Schemas\Components\He4rtSelect;
use App\Filament\Schemas\Components\He4rtToggle;
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
                                    ->description('The title and summary are the first things candidates see')
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
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.position_details'))
                                    ->description('Define where this role sits in the organization and how work will be conducted')
                                    ->icon(Heroicon::Briefcase)
                                    ->schema([
                                        He4rtSelect::make('department_id')
                                            ->label(__('recruitment::filament.requisition.fields.department'))
                                            ->relationship(
                                                name: 'department',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query, Get $get) => $query->when($get('team_id'), fn ($q) => $q->forTeam($get('team_id'))),
                                            )
                                            ->description('The team or division this role belongs to')
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
                                            ->description('The nature of the employment contract')
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
                                            ->description('How many positions should be available')
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),
                                        He4rtSelect::make('recruiter_id')
                                            ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                                            ->relationship(
                                                name: 'recruiter',
                                                modifyQueryUsing: fn ($query, Get $get) => $query->when($get('team_id'), fn ($q) => $q->forTeam($get('team_id'))),
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                                            ->icon(Heroicon::Users)
                                            ->iconColor('red')
                                            ->description('The official recruiter that owns this job requisition.')
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
                                    ->description('Provide a detailed description of the role and what the position involves')
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'descriptions',
                                            JobRequisitionItemTypeEnum::Description,
                                            __('recruitment::filament.requisition.job_posting.fields.description'),
                                        ),
                                    ]),
                            ]),
                        Tab::make(__('recruitment::filament.requisition.tabs.requirements'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('recruitment::filament.requisition.sections.required_qualifications'))
                                    ->description('Skills, experience, and qualifications that are required for this position')
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'requiredQualifications',
                                            JobRequisitionItemTypeEnum::RequiredQualification,
                                            __('recruitment::filament.requisition.job_posting.fields.required_qualifications'),
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.preferred_qualifications'))
                                    ->description('Nice-to-have skills that would give candidates an advantage')
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'preferredQualifications',
                                            JobRequisitionItemTypeEnum::PreferredQualification,
                                            __('recruitment::filament.requisition.job_posting.fields.preferred_qualifications'),
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.responsibilities'))
                                    ->description('Key duties and responsibilities for this role')
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'responsibilities',
                                            JobRequisitionItemTypeEnum::Responsibility,
                                            __('recruitment::filament.requisition.job_posting.fields.responsibilities'),
                                        ),
                                    ]),
                                Section::make(__('recruitment::filament.requisition.sections.benefits'))
                                    ->description('Perks, benefits, and what you offer to candidates')
                                    ->schema([
                                        self::makeItemsRepeater(
                                            'benefits',
                                            JobRequisitionItemTypeEnum::Benefit,
                                            __('recruitment::filament.requisition.job_posting.fields.benefits'),
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

                        Tab::make(__('recruitment::filament.requisition.tabs.misc'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('recruitment::filament.requisition.sections.company_info'))
                                    ->relationship('post')
                                    ->columns(2)
                                    ->schema([
                                        Hidden::make('team_id')
                                            ->default(filament()->getTenant()?->getKey()),
                                        Textarea::make('about_company')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.about_company'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('about_team')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.about_team'))
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('work_schedule')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.work_schedule'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Textarea::make('accessibility_accommodations')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.accessibility_accommodations'))
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        He4rtToggle::make('is_disability_confident')
                                            ->required()
                                            ->label(__('recruitment::filament.requisition.job_posting.fields.is_disability_confident'))
                                            ->default(false),
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
        string $label,
    ): Repeater {
        return Repeater::make($relationshipName)
            ->label($label)
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
