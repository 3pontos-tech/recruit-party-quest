<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Users\User;

class ViewJobRequisition extends ViewRecord
{
    protected static string $resource = JobRequisitionResource::class;

    protected Width|string|null $maxContentWidth = Width::ScreenExtraLarge;

    protected string $view = 'panel-app::filament.jobs.view-job';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var User|null $user */
        $user = auth()->user();

        if ($user?->candidate) {
            $application = $user->candidate->applications()
                ->where('requisition_id', $this->record->id)
                ->first();

            if ($application) {
                $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));
            }
        }
    }

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
