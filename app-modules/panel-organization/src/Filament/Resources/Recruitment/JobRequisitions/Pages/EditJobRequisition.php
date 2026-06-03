<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\DuplicateJobRequisitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\Support\Htmlable;

class EditJobRequisition extends EditRecord
{
    protected static string $resource = JobRequisitionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var JobRequisition $record */
        $record = $this->getRecord();

        return __('panel-organization::filament.job-requisitions.edit_title', ['title' => $record->post->title]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CopyJobShareLinkAction::make(),
            DuplicateJobRequisitionAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
