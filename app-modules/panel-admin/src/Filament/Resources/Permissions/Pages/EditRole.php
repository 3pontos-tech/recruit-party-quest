<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Permissions\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
