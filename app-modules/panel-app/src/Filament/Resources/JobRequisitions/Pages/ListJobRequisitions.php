<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;

class ListJobRequisitions extends ListRecords
{
    protected static string $resource = JobRequisitionResource::class;

    protected Width|string|null $maxContentWidth = Width::ScreenExtraLarge;

    protected string $view = 'panel-app::filament.jobs.list-jobs';

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }
}
