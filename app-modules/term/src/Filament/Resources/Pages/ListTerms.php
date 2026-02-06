<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Resources\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Term\Filament\Resources\TermResource;

class ListTerms extends ListRecords
{
    protected static string $resource = TermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
