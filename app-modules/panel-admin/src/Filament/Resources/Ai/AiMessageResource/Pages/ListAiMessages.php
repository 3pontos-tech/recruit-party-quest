<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiMessageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Ai\AiMessageResource;

final class ListAiMessages extends ListRecords
{
    protected static string $resource = AiMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
