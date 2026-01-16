<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\AiThreadFolderResource;

final class EditAiThreadFolder extends EditRecord
{
    protected static string $resource = AiThreadFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
