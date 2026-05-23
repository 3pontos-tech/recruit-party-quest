<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Feedback\Actions\StoreEvaluationAction;
use He4rt\Feedback\DTOs\CriteriaScoresDTO;
use He4rt\Feedback\DTOs\EvaluationDTO;
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
            ->visible(fn (Application $record): bool => ! $record->is_last_stage)
            ->disabled(fn (Application $record): bool => ! $record->current_step->canChange() || $record->is_last_stage)
            ->tooltip(fn (Application $record): ?string => $record->current_step->canChange() ? null : __('applications::filament.actions.change_status.no_transitions_tooltip'))
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
        // Etapa em que o candidato está sendo avaliado (a que ele deixa).
        // Capturada antes da transição; se não houver (ex.: candidatura New),
        // cai na etapa resultante após a transição.
        $evaluatedStageId = $record->current_stage_id;

        // Transição primeiro: garante que a avaliação só é gravada se a
        // movimentação for válida (sem avaliação órfã) e que há uma etapa
        // válida mesmo quando o candidato ainda não estava em nenhuma.
        $transitionData = TransitionData::fromArray($data, auth()->id());
        $record->current_step->handle($transitionData);

        $criteria = $data['criteria_scores'];
        resolve(StoreEvaluationAction::class)->execute(new EvaluationDTO(
            teamId: $data['team_id'],
            applicationId: $record->getKey(),
            stageId: $evaluatedStageId ?? $record->current_stage_id,
            evaluatorId: $data['evaluator_id'],
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
        $choices = $record->current_step->choices();

        $choices = Arr::except($choices, [
            ApplicationStatusEnum::OfferAccepted->value,
            ApplicationStatusEnum::OfferDeclined->value,
            ApplicationStatusEnum::Hired->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::OfferExtended->value,
        ]);

        return [
            Select::make('to_status')
                ->label(__('applications::filament.fields.target_status'))
                ->helperText(__('applications::filament.fields.target_status_hint'))
                ->options($choices)
                ->enum(ApplicationStatusEnum::class)
                ->required()
                ->live(),

            Select::make('to_stage_id')
                ->label(__('applications::filament.fields.target_stage'))
                ->helperText(__('applications::filament.fields.target_stage_hint'))
                ->options(fn () => ($record->requisition?->stages ?? collect()) // @phpstan-ignore nullsafe.neverNull
                    ->where('active', true)
                    ->where('display_order', '>', $record->currentStage?->display_order ?? 0) // @phpstan-ignore nullsafe.neverNull
                    ->pluck('name', 'id'))
                ->default($record->getNextStage()?->id)
                ->visible(fn (Get $get) => in_array($get('to_status'), [ApplicationStatusEnum::InProgress, ApplicationStatusEnum::OfferExtended])),

            Textarea::make('notes')
                ->label(__('applications::filament.fields.transition_notes'))
                ->rows(2),
            EvaluationForm::section(),
        ];

    }
}
