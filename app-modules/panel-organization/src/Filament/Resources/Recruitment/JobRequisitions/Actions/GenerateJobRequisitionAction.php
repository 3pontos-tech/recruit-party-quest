<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use App\Filament\Schemas\Components\He4rtSelect;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Events\JobRequisitionGenerationEvent;
use He4rt\Recruitment\Requisitions\Jobs\GeneratePostJob;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class GenerateJobRequisitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.generate_job_requisition.label'))
            ->icon(Heroicon::OutlinedAcademicCap)
            ->color('primary')
            ->extraAttributes(fn () => [
                'class' => 'w-full',
            ])
            ->outlined()
            ->requiresConfirmation()
            ->modalWidth(Width::FitContent)
            ->modalIcon(Heroicon::OutlinedAcademicCap)
            ->modalHeading(__('panel-organization::filament.actions.generate_job_requisition.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.generate_job_requisition.modal_description'))
            ->schema($this->formSchema())
            ->action(function (array $data, Action $action): void {
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

                $queueConnection = config('queue.default');

                try {
                    Queue::connection($queueConnection)->size('default');
                } catch (Exception $exception) {
                    Log::error('Queue health check failed before dispatching GeneratePostJob', [
                        'user_id' => auth()->id(),
                        'connection' => $queueConnection,
                        'error' => $exception->getMessage(),
                        'trace' => $exception->getTraceAsString(),
                    ]);

                    Notification::make()
                        ->danger()
                        ->title(__('panel-organization::view.job_generation.queue_unavailable_title'))
                        ->body(__('panel-organization::view.job_generation.queue_unavailable_message'))
                        ->send();

                    return;
                }

                broadcast(new JobRequisitionGenerationEvent(
                    JobGenerationStatus::Processing,
                    (string) auth()->user()->getKey()
                ));

                dispatch(new GeneratePostJob($dto));

                Notification::make()
                    ->info()
                    ->title(__('panel-organization::filament.actions.generate_job_requisition.processing_title'))
                    ->body(__('panel-organization::filament.actions.generate_job_requisition.processing_body'))
                    ->send();

                $action->success();
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
                    Grid::make()
                        ->columns(2)
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
                                    ) => $query->when(
                                        $get('team_id'),
                                        /** @phpstan-ignore-next-line */
                                        fn (Builder $q) => $q->forTeam($get('team_id'))
                                    ),
                                )
                                ->description(__('recruitment::filament.requisition.fields.department_description'))
                                ->icon(Heroicon::BuildingOffice)
                                ->iconColor('purple')
                                ->required()
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
                                    ) => $query->when(
                                        $get('team_id'),
                                        /** @phpstan-ignore-next-line */
                                        fn (Builder $q) => $q->forTeam($get('team_id'))
                                    ),
                                )
                                ->getOptionLabelFromRecordUsing(
                                    fn (Recruiter $record) => $record->user->name
                                )
                                ->description(__('recruitment::filament.requisition.fields.hiring_manager_description'))
                                ->icon(Heroicon::Users)
                                ->iconColor('red')
                                ->required()
                                ->preload()
                                ->searchable(),
                        ]),

                    Grid::make()
                        ->columns(2)
                        ->schema([
                            He4rtSelect::make('work_arrangement')
                                ->label(__('recruitment::filament.requisition.fields.work_arrangement'))
                                ->options(WorkArrangementEnum::class)
                                ->description(__('panel-organization::filament.actions.generate_job_requisition.fields.work_arrangement_description'))
                                ->icon(Heroicon::Home)
                                ->iconColor('red')
                                ->native(false)
                                ->required(),

                            He4rtSelect::make('employment_type')
                                ->label(__('recruitment::filament.requisition.fields.employment_type'))
                                ->options(EmploymentTypeEnum::class)
                                ->description(__('recruitment::filament.requisition.fields.employment_type_description'))
                                ->icon(Heroicon::Clock)
                                ->iconColor('green')
                                ->native(false)
                                ->required(),
                        ]),

                    Grid::make()
                        ->columns(2)
                        ->schema([
                            He4rtSelect::make('experience_level')
                                ->label(__('recruitment::filament.requisition.fields.experience_level'))
                                ->options(ExperienceLevelEnum::class)
                                ->description(__('panel-organization::filament.actions.generate_job_requisition.fields.experience_level_description'))
                                ->icon(Heroicon::CheckBadge)
                                ->iconColor('yellow')
                                ->native(false)
                                ->required(),

                            He4rtSelect::make('priority')
                                ->label(__('recruitment::filament.requisition.fields.priority'))
                                ->options(RequisitionPriorityEnum::class)
                                ->description(__('panel-organization::filament.actions.generate_job_requisition.fields.priority_description'))
                                ->icon(Heroicon::Cube)
                                ->iconColor('yellow')
                                ->default(RequisitionPriorityEnum::Medium)
                                ->required(),
                        ]),
                ]),
        ];
    }
}
