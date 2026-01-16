<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Candidates\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Candidates\CandidateResource;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;
}
