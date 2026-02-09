<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use App\Filament\Schemas\Components\He4rtSelect;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Jobs\GeneratePostJob;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Builder;

class GenerateJobRequisitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.generate_job_requisition.label'))
            ->icon(Heroicon::OutlinedAcademicCap)
            ->color('primary')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->requiresConfirmation()
            ->modalWidth(Width::FitContent)
            ->modalIcon(Heroicon::OutlinedAcademicCap)
            ->modalHeading(__('panel-organization::filament.actions.generate_job_requisition.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.generate_job_requisition.modal_description'))
            ->schema($this->formSchema())
            ->action(function (array $data): void {
                /** @var Team $team */
                $team = filament()->getTenant();
                $dto = GenerateJobRequisitionDTO::make([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'work_arrangement' => $data['work_arrangement']->value,
                    'employment_type' => $data['employment_type']->value,
                    'experience_level' => $data['experience_level']->value,
                    'priority' => $data['priority']->value,
                    'recruiter_id' => $data['recruiter_id'],
                    'created_by' => auth()->user()->getKey(),
                    'company_description' => $team->about,
                    'department_id' => $data['department_id'],
                    'team_id' => $team->getKey(),
                ]);

                dispatch(new GeneratePostJob($dto));

                Notification::make()
                    ->success()
                    ->title(__('recruitment::filament.requisition.job_posting.notifications.generating'))
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'generate-job-requisition-action';
    }

    /**
     * @return array<int, Field|Section|RichEditor>
     */
    private function formSchema(): array
    {
        return [
            TextInput::make('title')
                ->label(__('recruitment::filament.requisition.job_posting.fields.title'))
                ->required()
                ->live(debounce: 700)
                ->columnSpanFull(),
            RichEditor::make('description')
                ->required()
                ->label(__('recruitment::filament.requisition.job_posting.fields.description'))
                ->columnSpanFull(),
            Section::make(__('recruitment::filament.requisition.sections.position_details'))
                ->description(__('recruitment::filament.requisition.sections.position_details_description'))
                ->icon(Heroicon::Briefcase)
                ->schema([
                    He4rtSelect::make('department_id')
                        ->label(__('recruitment::filament.requisition.fields.department'))
                        ->relationship(
                            name: 'department',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (
                                Builder $query,
                                Get $get
                                /** @phpstan-ignore-next-line */
                            ) => $query->when($get('team_id'),
                                /** @phpstan-ignore-next-line */
                                fn (Builder $q) => $q->forTeam($get('team_id'))),
                        )
                        ->createOptionAction(fn (Action $action): Action => $action
                            ->modalHeading(__('teams::filament.relation_managers.departments.title'))
                            ->authorize('create', Department::class))
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('teams::filament.department.fields.name'))
                                ->required()
                                ->maxLength(255),
                            Textarea::make('description')
                                ->label(__('teams::filament.department.fields.description'))
                                ->required()
                                ->maxLength(255)
                                ->rows(4),
                            He4rtSelect::make('head_user_id')
                                ->label(__('teams::filament.department.fields.head_user'))
                                ->relationship(
                                    name: 'headUser',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                        ->whereHas('teams', function (Builder $teamQuery) use ($get): void {
                                            $teamId = $get('team_id') ?? filament()->getTenant()?->getKey();
                                            if (! $teamId) {
                                                return;
                                            }

                                            $teamQuery->whereKey($teamId);
                                        })
                                        ->orderBy('name'),
                                )
                                ->searchable()
                                ->preload(),
                            Hidden::make('team_id')
                                ->default(fn (Get $get) => $get('team_id') ?? filament()->getTenant()?->getKey())
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data): string {
                            /** @var Department $department */
                            $department = Department::query()->create($data);

                            return (string) $department->getKey();
                        })
                        ->description(__('recruitment::filament.requisition.fields.department_description'))
                        ->icon(Heroicon::BuildingOffice)
                        ->required()
                        ->iconColor('purple')
                        ->preload()
                        ->searchable(),
                    He4rtSelect::make('recruiter_id')
                        ->label(__('recruitment::filament.requisition.fields.hiring_manager'))
                        ->relationship(
                            name: 'recruiter',
                            modifyQueryUsing: fn (
                                Builder $query,
                                Get $get
                                /** @phpstan-ignore-next-line */
                            ) => $query->when($get('team_id'),
                                /** @phpstan-ignore-next-line */
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
                    He4rtSelect::make('priority')
                        ->label(__('recruitment::filament.requisition.fields.priority'))
                        ->description('How fast we must close this position?')
                        ->icon(Heroicon::Cube)
                        ->iconColor('yellow')
                        ->options(RequisitionPriorityEnum::class)
                        ->default(RequisitionPriorityEnum::Medium)
                        ->required(),
                ]),
        ];
    }
}
