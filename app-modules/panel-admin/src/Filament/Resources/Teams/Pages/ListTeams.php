<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Teams\TeamResource;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
