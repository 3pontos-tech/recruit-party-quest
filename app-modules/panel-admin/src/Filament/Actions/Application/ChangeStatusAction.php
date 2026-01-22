<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Actions\Application;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;

/**
 * @method Application getRecord()
 */
final class ChangeStatusAction
{
    public static function make(): Action
    {
        // TODO: essa action pravavelmente não deve ser mais util.
        return Action::make('change-status')
            ->label(__('applications::filament.actions.change_status.label'))
            ->disabled(fn (Application $record) => ! $record->current_step->canChange())
            ->tooltip(fn (Application $record) => $record->current_step->canChange() ? null : __('applications::filament.actions.change_status.no_transitions_tooltip'))
            ->schema([
                Select::make('to_status')
                    ->label(__('applications::filament.fields.status'))
                    ->options(fn (Application $record) => $record->current_step->choices())
                    ->enum(ApplicationStatusEnum::class)
                    ->reactive()
                    ->required(),

                Select::make('rejection_reason_category')
                    ->label(__('applications::filament.fields.rejection_reason_category'))
                    ->options(RejectionReasonCategoryEnum::class)
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::Rejected->value),

                Textarea::make('rejection_reason_details')
                    ->label(__('applications::filament.fields.rejection_reason_details'))
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::Rejected->value),

                TextInput::make('offer_amount')
                    ->label(__('applications::filament.fields.offer_amount'))
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::OfferExtended->value),

                DateTimePicker::make('offer_response_deadline')
                    ->label(__('applications::filament.fields.offer_response_deadline'))
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::OfferExtended->value),

                // TODO: fazer uma query e mostrar os stages
                TextInput::make('to_stage_id')
                    ->label(__('applications::filament.fields.current_stage'))
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::InProgress->value),

                Textarea::make('notes')
                    ->label(__('applications::filament.fields.transition_notes'))
                    ->rows(3),
            ])
            ->modalHeading(__('applications::filament.actions.change_status.modal_heading'))
            ->modalButton(__('applications::filament.actions.change_status.modal_submit'))
            ->action(function (array $data): void {
                /** @phpstan-ignore-next-line  */
                $application = $this->getRecord(); // todo: não sei se esse get record funciona

                //                $this->authorize('transition', [$application, $data['to_status']]);

                $application->current_step->handle(auth()->user(), $data);

                Notification::make()->title(__('applications::filament.actions.change_status.notifications.updated.title'))->success()->send();

                //                $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->id]));
            });
    }
}
