<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use He4rt\Applications\Actions\MarkApplicationAsViewed;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas\ApplicationInfolist;
use He4rt\Users\User;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $application = $this->getRecord();
        $user = auth()->user();

        if ($application instanceof Application && $user instanceof User) {
            resolve(MarkApplicationAsViewed::class)->execute($application, $user);
        }
    }
}
