<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\AiAssistantResource;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\Forms\AiAssistantForm;

final class CreateAiAssistant extends CreateRecord
{
    protected static string $resource = AiAssistantResource::class;

    public function form(Schema $schema): Schema
    {
        return resolve(AiAssistantForm::class)->form($schema);
    }
}
