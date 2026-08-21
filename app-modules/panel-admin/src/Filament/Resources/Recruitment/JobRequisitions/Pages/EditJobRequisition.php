<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Applications\Filament\Actions\ExportJobApplicationsAction;

class EditJobRequisition extends EditRecord
{
    protected static string $resource = JobRequisitionResource::class;

    protected function getHeaderActions(): array
    {

        return [
            ExportJobApplicationsAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
