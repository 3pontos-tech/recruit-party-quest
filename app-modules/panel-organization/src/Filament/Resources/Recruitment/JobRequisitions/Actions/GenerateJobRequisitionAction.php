<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;

class GenerateJobRequisitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.x.label'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading(__('panel-organization::filament.actions.x.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.x.modal_description'))
            ->schema($this->formSchema())
            ->action(function (array $data): void {
                // resolve(GenerateJobRequisition::class)->execute();
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

    /**
     * @return array<int, Field>
     */
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
