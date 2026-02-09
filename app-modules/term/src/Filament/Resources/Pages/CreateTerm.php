<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Term\Filament\Resources\TermResource;

class CreateTerm extends CreateRecord
{
    protected static string $resource = TermResource::class;
}
