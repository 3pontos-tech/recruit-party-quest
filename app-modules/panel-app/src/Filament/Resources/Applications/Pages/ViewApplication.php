<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\Applications\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;
use He4rt\App\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Users\User;

/**
 * @method Application getRecord()
 */
class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected Width|string|null $maxContentWidth = Width::ScreenExtraLarge;

    protected string $view = 'panel-app::filament.applications.view-application';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $application = $this->getRecord();
        $requisition = $application->requisition;

        if ($requisition->status !== RequisitionStatusEnum::Published) {
            /** @var User|null $user */
            $user = auth()->user();
            $isApplicant = $application->candidate->user_id === $user?->getKey();

            if (! $isApplicant) {
                $this->redirect(JobRequisitionResource::getUrl('index'));
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

    protected function getViewData(): array
    {
        return [
            'jobRequisition' => $this->getRecord()->requisition,
            'currentStage' => $this->getRecord()->currentStage,
            'stages' => $this->getRecord()->requisition?->stages ?? collect(), // @phpstan-ignore nullsafe.neverNull
        ];
    }
}
