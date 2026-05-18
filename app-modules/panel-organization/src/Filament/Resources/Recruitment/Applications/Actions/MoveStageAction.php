<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Feedback\Actions\StoreEvaluationAction;
use He4rt\Feedback\DTOs\CriteriaScoresDTO;
use He4rt\Feedback\DTOs\EvaluationDTO;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\EvaluationForm;
use Illuminate\Support\Arr;

class MoveStageAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->outlined()
            ->label(__('applications::filament.actions.move_stage.label'))
            ->icon('heroicon-o-arrow-right-circle')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading(__('applications::filament.actions.move_stage.modal_heading'))
            ->modalSubmitActionLabel(__('applications::filament.actions.move_stage.modal_submit'))
            ->visible(fn (Application $record): bool => ! $record->is_last_stage)
            ->disabled(fn (Application $record): bool => ! $record->current_step->canChange() || $record->is_last_stage)
            ->tooltip(fn (Application $record): ?string => $record->current_step->canChange() ? null : __('applications::filament.actions.move_stage.no_transitions_tooltip'))
            ->schema($this->buildSchema(...))
            ->action($this->processAction(...))
            ->requiresConfirmation();
    }

    public static function getDefaultName(): ?string
    {
        return 'move-stage-action';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function processAction(Application $record, array $data): void
    {
        try {
            $transitionData = TransitionData::fromArray($data, auth()->id());
            $record->current_step->handle($transitionData);
        } catch (InvalidTransitionException|MissingTransitionDataException $e) {
            Notification::make()
                ->danger()
                ->title(__('applications::filament.actions.move_stage.notifications.error.title'))
                ->body($e->getMessage())
                ->send();

            return;
        }

        if (($data['with_evaluation'] ?? false) === true) {
            $criteria = $data['criteria_scores'];
            resolve(StoreEvaluationAction::class)->execute(new EvaluationDTO(
                teamId: $data['team_id'],
                applicationId: $record->getKey(),
                stageId: $record->current_stage_id,
                evaluatorId: $data['evaluator_id'],
                overallRating: $data['overall_rating'],
                recommendation: $data['recommendation'],
                strengths: $data['strengths'],
                concerns: $data['concerns'],
                notes: $data['notes'] ?? null,
                criteriaScores: CriteriaScoresDTO::make([
                    'technical_skills' => $criteria['technical_skills'],
                    'communication' => $criteria['communication'],
                    'problem_solving' => $criteria['problem_solving'],
                    'culture_fit' => $criteria['culture_fit'],
                ]),
            ));
        }

        Notification::make()
            ->success()
            ->title(__('applications::filament.actions.move_stage.notifications.moved.title'))
            ->send();
    }

    /** @return array<int, Component> */
    private function buildSchema(Application $record): array
    {
        $choices = Arr::except($record->current_step->choices(), [
            ApplicationStatusEnum::OfferAccepted->value,
            ApplicationStatusEnum::OfferDeclined->value,
            ApplicationStatusEnum::Hired->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::OfferExtended->value,
            ApplicationStatusEnum::Withdrawn->value,
        ]);

        return [
            Select::make('to_status')
                ->label(__('applications::filament.fields.status'))
                ->options($choices)
                ->enum(ApplicationStatusEnum::class)
                ->required()
                ->live(),

            Select::make('to_stage_id')
                ->label(__('applications::filament.fields.current_stage'))
                ->options(fn () => ($record->requisition?->stages ?? collect()) // @phpstan-ignore nullsafe.neverNull
                    ->where('active', true)
                    ->where('display_order', '>', $record->currentStage?->display_order ?? 0) // @phpstan-ignore nullsafe.neverNull
                    ->pluck('name', 'id'))
                ->default($record->getNextStage()?->id)
                ->visible(function (Get $get): bool {
                    $status = $get('to_status');
                    $value = $status instanceof BackedEnum ? $status->value : (string) $status;

                    return in_array($value, [
                        ApplicationStatusEnum::InProgress->value,
                        ApplicationStatusEnum::OfferExtended->value,
                    ], true);
                }),

            Textarea::make('notes')
                ->label(__('applications::filament.fields.transition_notes'))
                ->rows(2),

            Toggle::make('with_evaluation')
                ->label(__('applications::filament.actions.move_stage.with_evaluation_label'))
                ->default(false)
                ->live(),

            Section::make()
                ->hiddenLabel()
                ->visible(fn (Get $get): bool => (bool) $get('with_evaluation'))
                ->schema(EvaluationForm::make()),
        ];
    }
}
