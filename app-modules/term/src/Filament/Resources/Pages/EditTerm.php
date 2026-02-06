<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Resources\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Term\Filament\Resources\TermResource;

class EditTerm extends EditRecord
{
    protected static string $resource = TermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
