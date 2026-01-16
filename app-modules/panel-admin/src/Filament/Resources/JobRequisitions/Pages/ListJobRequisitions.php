<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\JobRequisitions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\JobRequisitions\JobRequisitionResource;

class ListJobRequisitions extends ListRecords
{
    protected static string $resource = JobRequisitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
