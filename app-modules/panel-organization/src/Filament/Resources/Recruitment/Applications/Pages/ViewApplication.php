<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\ApplicationInfolist;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    protected function getActions(): array
    {
        return [
            Action::make('fodase')
                ->modal()
                ->modalContent(fn () => 'eaeeaeae')
                ->requiresConfirmation()
                ->action(fn () => true)
                ->label('caralho fodase'),
        ];
    }
}
