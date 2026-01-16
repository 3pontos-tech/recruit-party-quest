<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Ai\AiThreadsFolder\AiThreadFolderResource;

final class CreateAiThreadFolder extends CreateRecord
{
    protected static string $resource = AiThreadFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
