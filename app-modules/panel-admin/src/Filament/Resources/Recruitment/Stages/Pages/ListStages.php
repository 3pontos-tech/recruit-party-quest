<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Stages\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Recruitment\Stages\StageResource;

class ListStages extends ListRecords
{
    protected static string $resource = StageResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
