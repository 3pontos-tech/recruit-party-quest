<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\JobRequisitions\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\JobRequisitions\JobRequisitionResource;

class CreateJobRequisition extends CreateRecord
{
    protected static string $resource = JobRequisitionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();

        return $data;
    }
}
