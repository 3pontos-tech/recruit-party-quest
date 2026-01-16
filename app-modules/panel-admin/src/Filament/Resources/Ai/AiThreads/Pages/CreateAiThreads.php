<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\AiThreadsResource;

final class CreateAiThreads extends CreateRecord
{
    protected static string $resource = AiThreadsResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
