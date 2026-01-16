<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiThreads\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use He4rt\Admin\Filament\Resources\Ai\AiThreads\AiThreadsResource;

final class EditAiThreads extends EditRecord
{
    protected static string $resource = AiThreadsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
