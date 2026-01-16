<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\AiThreadsResource;

final class ListAiThreads extends ListRecords
{
    protected static string $resource = AiThreadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
