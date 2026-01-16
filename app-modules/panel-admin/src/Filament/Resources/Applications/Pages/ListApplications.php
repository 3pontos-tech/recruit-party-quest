<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Applications\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Applications\ApplicationResource;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
