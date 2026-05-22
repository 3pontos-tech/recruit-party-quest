<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions;

use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use He4rt\Recruitment\Requisitions\Actions\DuplicateJobRequisitionAction as DuplicateJobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DuplicateJobRequisitionBulkAction extends BulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('panel-organization::filament.actions.duplicate_job_requisition.bulk.label'))
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->authorize('create')
            ->requiresConfirmation()
            ->modalIcon(Heroicon::OutlinedDocumentDuplicate)
            ->modalHeading(__('panel-organization::filament.actions.duplicate_job_requisition.bulk.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.duplicate_job_requisition.bulk.modal_description'))
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $duplicator = resolve(DuplicateJobRequisition::class);
                $createdById = (string) auth()->id();

                $duplicatable = $records
                    ->whereInstanceOf(JobRequisition::class)
                    ->reject->trashed();

                DB::transaction(fn () => $duplicatable->each(
                    fn (JobRequisition $record) => $duplicator->execute($record, $createdById),
                ));

                Notification::make()
                    ->success()
                    ->title(__('panel-organization::filament.actions.duplicate_job_requisition.bulk.notification', [
                        'count' => $duplicatable->count(),
                    ]))
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'duplicate';
    }
}
