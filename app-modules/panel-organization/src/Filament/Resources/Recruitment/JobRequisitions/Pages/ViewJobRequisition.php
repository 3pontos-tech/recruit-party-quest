<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use He4rt\Applications\Filament\Actions\ExportJobApplicationsAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\DuplicateJobRequisitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Schemas\JobRequisitionInfolist;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Contracts\Support\Htmlable;

class ViewJobRequisition extends ViewRecord
{
    protected static string $resource = JobRequisitionResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var JobRequisition $record */
        $record = $this->getRecord();

        return $record->post->title;
    }

    public function infolist(Schema $schema): Schema
    {
        return JobRequisitionInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            CopyJobShareLinkAction::make(),
            ExportJobApplicationsAction::make(),
            DuplicateJobRequisitionAction::make(),
        ];
    }
}
