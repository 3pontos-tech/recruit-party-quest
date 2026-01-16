<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\CandidateResource;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
