<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiMessageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Ai\AiMessageResource;

final class CreateAiMessage extends CreateRecord
{
    protected static string $resource = AiMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
