<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;

class RejectApplicationAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.reject_application.label'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->requiresConfirmation()
            ->visible(fn (Application $record) => $record->status !== ApplicationStatusEnum::Rejected)
            ->modalHeading(__('panel-organization::filament.actions.reject_application.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.reject_application.modal_description'))
            ->schema($this->formSchema())
            ->action(function (array $data, Application $record): void {
                resolve(\He4rt\Applications\Actions\RejectApplicationAction::class)->execute(
                    $record->getKey(),
                    $data['rejection_reason_category'],
                    $data['rejection_reason_details'],
                );
                Notification::make()
                    ->danger()
                    ->title('User rejected successfully')
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'reject_application-action';
    }

    private function formSchema(): array
    {
        return [
            Select::make('rejection_reason_category')
                ->label(__('applications::filament.fields.rejection_reason_category'))
                ->options(RejectionReasonCategoryEnum::class)
                ->enum(RejectionReasonCategoryEnum::class)
                ->required(),
            Textarea::make('rejection_reason_details')
                ->label(__('applications::filament.fields.rejection_reason_details'))
                ->rows(3),
        ];
    }
}
