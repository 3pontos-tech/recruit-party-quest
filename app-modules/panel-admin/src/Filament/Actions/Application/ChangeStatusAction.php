<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Actions\Application;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;

final class ChangeStatusAction
{
    public static function make(): Action
    {
        return Action::make('change-status')
            ->label('Change status')
            ->disabled(fn (Application $record) => ! $record->current_step->canChange())
            ->tooltip(fn (Application $record
            ) => $record->current_step->canChange() ? null : 'No available status transitions')
            ->schema([
                FormSelect::make('to_status')
                    ->label(__('applications::filament.fields.status'))
                    ->options(fn (Application $record) => $record->current_step->choices())
                    ->enum(ApplicationStatusEnum::class)
                    ->reactive()
                    ->required(),

                FormSelect::make('rejection_reason_category')
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

                TextInput::make('to_stage_id')
                    ->label(__('applications::filament.fields.current_stage'))
                    ->visible(fn ($get) => $get('to_status') === ApplicationStatusEnum::InProgress->value),

                Textarea::make('notes')
                    ->label(__('applications::filament.fields.transition_notes'))
                    ->rows(3),
            ])
            ->modalHeading('Change application status')
            ->modalButton('Confirm')
            ->action(function (array $data): void {
                $application = $this->getRecord(); // todo: não sei se esse get record funciona

                //                $this->authorize('transition', [$application, $data['to_status']]);

                $application->current_step->handle($data, auth()->user());

                Notification::make()->title('Status updated')->success()->send();

                //                $this->redirect($this->getResource()::getUrl('edit', ['record' => $application->id]));
            });
    }
}
