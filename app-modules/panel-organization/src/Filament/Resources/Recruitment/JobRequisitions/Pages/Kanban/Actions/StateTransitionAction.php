<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Feedback\Actions\StoreEvaluationAction;
use He4rt\Feedback\DTOs\CriteriaScoresDTO;
use He4rt\Feedback\DTOs\EvaluationDTO;
use He4rt\Organization\Filament\Forms\Components\StageTimeline;
use He4rt\Organization\Filament\Forms\Components\StatusHeroBand;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\EvaluationForm;
use Illuminate\Support\Arr;

class StateTransitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->outlined()
            ->label(__('applications::filament.actions.change_status.label'))
            ->icon('heroicon-o-play')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalWidth(Width::FourExtraLarge)
            ->disabled(fn (Application $record): bool => ! $record->current_step->canChange())
            ->tooltip(fn (Application $record): ?string => $record->current_step->canChange() ? null : (string) __('applications::filament.actions.change_status.no_transitions_tooltip'))
            ->schema($this->buildSchema(...))
            ->action($this->processAction(...))
            ->requiresConfirmation();
    }

    public static function getDefaultName(): ?string
    {
        return 'state-transition-action';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function processAction(Application $record, array $data): void
    {
        $evaluatedStageId = $record->current_stage_id;

        $userId = auth()->id();
        $transitionData = TransitionData::fromArray($data, $userId !== null ? (string) $userId : null);
        $record->current_step->handle($transitionData);

        if ($data['with_evaluation'] ?? false) {
            $this->recordEvaluation($record, $data, $evaluatedStageId);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function recordEvaluation(Application $record, array $data, ?string $evaluatedStageId): void
    {
        $criteria = $data['criteria_scores'];

        resolve(StoreEvaluationAction::class)->execute(new EvaluationDTO(
            teamId: $record->team_id,
            applicationId: $record->getKey(),
            stageId: $evaluatedStageId ?? $record->current_stage_id,
            evaluatorId: auth()->user()->getKey(),
            overallRating: $data['overall_rating'],
            recommendation: $data['recommendation'],
            strengths: $data['strengths'],
            concerns: $data['concerns'],
            notes: $data['notes'],
            criteriaScores: CriteriaScoresDTO::make([
                'technical_skills' => $criteria['technical_skills'],
                'communication' => $criteria['communication'],
                'problem_solving' => $criteria['problem_solving'],
                'culture_fit' => $criteria['culture_fit'],
            ]),
        ));
    }

    /** @return array<int, Component> */
    private function buildSchema(Application $record): array
    {
        $choices = Arr::except(
            $record->current_step->choices(),
            [ApplicationStatusEnum::Rejected->value],
        );

        return [
            StatusHeroBand::make('to_status')
                ->label(__('applications::filament.fields.target_status'))
                ->helperText(__('applications::filament.fields.target_status_hint'))
                ->options($choices)
                ->enum(ApplicationStatusEnum::class)
                ->required()
                ->live(),

            TextInput::make('offer_amount')
                ->label(__('applications::filament.fields.offer_amount'))
                ->numeric()
                ->prefix('R$')
                ->visible(fn (Get $get): bool => $get('to_status') === ApplicationStatusEnum::OfferExtended),

            DatePicker::make('offer_response_deadline')
                ->label(__('applications::filament.fields.offer_response_deadline'))
                ->visible(fn (Get $get): bool => $get('to_status') === ApplicationStatusEnum::OfferExtended),

            StageTimeline::make('to_stage_id')
                ->label(__('applications::filament.fields.target_stage'))
                ->helperText(__('applications::filament.fields.target_stage_hint'))
                ->fromStageLabel($record->currentStage?->name)
                ->options(fn () => ($record->requisition?->stages ?? collect()) // @phpstan-ignore nullsafe.neverNull
                    ->where('active', true)
                    ->where('display_order', '>', $record->currentStage?->display_order ?? 0) // @phpstan-ignore nullsafe.neverNull
                    ->pluck('name', 'id'))
                ->default($record->getNextStage()?->id)
                ->visible(fn (Get $get): bool => $get('to_status') === ApplicationStatusEnum::InProgress),

            Textarea::make('notes')
                ->label(__('applications::filament.fields.transition_notes'))
                ->rows(2),

            Toggle::make('with_evaluation')
                ->label(__('applications::filament.actions.change_status.with_evaluation_label'))
                ->default(false)
                ->live(),

            EvaluationForm::section()
                ->visible(fn (Get $get): bool => (bool) $get('with_evaluation')),
        ];

    }
}
