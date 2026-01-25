<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;

/**
 * @property JobRequisition $record
 */
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
                ->where('requisition_id', $this->record->getKey())
                ->first();

            if ($application) {
                $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));
            }
        }
    }

    /**
     * Apply directly to the job requisition (for jobs without screening questions).
     */
    public function applyDirectly(ApplyToJobRequisitionAction $action): void
    {
        $user = auth()->user();

        if (! $user?->candidate) {
            return;
        }

        if ($action->hasApplied($this->record, $user->candidate)) {
            return;
        }

        $application = $action->execute($this->record, $user->candidate);

        $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));
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
