<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\AiThreadFolderResource;

final class ListAiThreadFolders extends ListRecords
{
    protected static string $resource = AiThreadFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
