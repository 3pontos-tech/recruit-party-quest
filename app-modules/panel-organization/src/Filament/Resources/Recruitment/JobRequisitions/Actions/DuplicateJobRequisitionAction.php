<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Recruitment\Requisitions\Actions\DuplicateJobRequisitionAction as DuplicateJobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class DuplicateJobRequisitionAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.duplicate_job_requisition.label'))
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->authorize('create')
            ->visible(fn (JobRequisition $record): bool => ! $record->trashed())
            ->requiresConfirmation()
            ->modalIcon(Heroicon::OutlinedDocumentDuplicate)
            ->modalHeading(__('panel-organization::filament.actions.duplicate_job_requisition.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.duplicate_job_requisition.modal_description'))
            ->action(function (JobRequisition $record): void {
                $duplicate = resolve(DuplicateJobRequisition::class)->execute($record, (string) auth()->id());

                Notification::make()
                    ->success()
                    ->title(__('panel-organization::filament.actions.duplicate_job_requisition.notification'))
                    ->send();

                redirect(JobRequisitionResource::getUrl('edit', ['record' => $duplicate]));
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'duplicate';
    }
}
