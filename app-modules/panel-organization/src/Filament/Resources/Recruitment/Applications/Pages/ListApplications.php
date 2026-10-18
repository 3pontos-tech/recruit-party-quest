<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\ApplicationResource;
use He4rt\Organization\Livewire\Applications\ApplicationsWorkspace;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Livewire::make(ApplicationsWorkspace::class),
        ]);
    }
}
