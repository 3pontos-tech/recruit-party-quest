<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\JobRequisitions\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Exceptions\RequisitionNotPublishedException;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Users\User;
use Illuminate\Contracts\Support\Htmlable;

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
        $requisitionId = JobPosting::query()
            ->where('slug', $record)
            ->firstOrFail()
            ->job_requisition_id;

        parent::mount($requisitionId);

        /** @var User|null $user */
        $user = auth()->user();

        if ($user?->candidate) {
            $application = $this->record->applicationFrom($user->candidate);

            if ($application) {
                $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));

                return;
            }
        }

        if (! $this->record->isPublished()) {
            $this->redirectToUnavailableJobsList();
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

        if ($this->record->applicationFrom($user->candidate)) {
            return;
        }

        try {
            $application = $action->execute($this->record, $user->candidate);
        } catch (RequisitionNotPublishedException) {
            $this->redirectToUnavailableJobsList();

            return;
        }

        $this->redirect(ApplicationResource::getUrl('view', ['record' => $application]));
    }

    public function getTitle(): string|Htmlable
    {
        /** @var JobRequisition $record */
        $record = $this->getRecord();

        return $record->post->title;
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

    /**
     * Warn the candidate the job is unavailable and send them back to the jobs list.
     */
    private function redirectToUnavailableJobsList(): void
    {
        Notification::make()
            ->title(__('panel-app::filament.pages.job_description.job_unavailable'))
            ->warning()
            ->send();

        $this->redirect(JobRequisitionResource::getUrl('index'));
    }
}
