<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;

class StateTransitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->outlined()
            ->label(__('applications::filament.actions.change_status.label'))
            ->icon('heroicon-o-play')
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
        $transitionData = TransitionData::fromArray($data, auth()->id());
        $record->current_step->handle($transitionData);
    }

    /** @return array<int, Field> */
    private function buildSchema(Application $record): array
    {

        $choices = $record->current_step->choices();

        return [
            Select::make('to_status')
                ->label(__('applications::filament.fields.status'))
                ->options($choices)
                ->enum(ApplicationStatusEnum::class)
                ->required()
                ->live(),

            Select::make('to_stage_id')
                ->label(__('applications::filament.fields.current_stage'))
                ->options(fn () => $record->requisition->stages
                    ->where('active', true)
                    ->where('display_order', '>', $record->currentStage->display_order)
                    ->pluck('name', 'id'))
                ->default($record->getNextStage()->id)
                ->visible(fn (Get $get) => in_array($get('to_status'), [ApplicationStatusEnum::InProgress, ApplicationStatusEnum::OfferExtended])),

            Textarea::make('rejection_reason_details')
                ->label(__('applications::filament.fields.rejection_reason_details'))
                ->rows(3)
                ->visible(fn (Get $get) => $get('to_status') === ApplicationStatusEnum::Rejected)
                ->required(fn (Get $get) => $get('to_status') === ApplicationStatusEnum::Rejected),

            Textarea::make('notes')
                ->label(__('applications::filament.fields.transition_notes'))
                ->rows(2),
        ];

    }
}
