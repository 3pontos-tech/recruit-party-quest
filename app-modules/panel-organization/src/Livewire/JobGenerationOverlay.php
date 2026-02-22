<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire;

use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class JobGenerationOverlay extends Component
{
    public string $state = 'idle';
    #[Locked]
    public ?string $jobRequisitionId = null;

    public function onProcessing(): void
    {
        $this->state = JobGenerationStatus::Processing->value;
    }

    /**
     * @param  array{status: string, job_requisition_id: ?string, error_message: ?string}  $event
     */
    public function onSuccess(array $event): void
    {
        $this->state = JobGenerationStatus::Success->value;
        $this->jobRequisitionId = $event['job_requisition_id'] ?? null;

        $this->dispatch('redirect-after-delay');
    }

    public function onError(): void
    {
        $this->state = JobGenerationStatus::Error->value;
    }

    public function onTimeout(): void
    {
        $this->state = JobGenerationStatus::Error->value;

        $this->dispatch('notify', [
            'type' => 'warning',
            'title' => __('panel-organization::view.job_generation.timeout_title'),
            'message' => __('panel-organization::view.job_generation.timeout_message'),
            'persistent' => true,
        ]);
    }

    public function closeOverlay(): void
    {
        $this->state = 'idle';
        $this->jobRequisitionId = null;
    }

    public function redirectToEdit(): void
    {
        if ($this->jobRequisitionId) {
            $this->redirect(EditJobRequisition::getUrl([
                'tenant' => filament()->getTenant(),
                'record' => $this->jobRequisitionId,
            ]));
        }
    }

    public function render(): Factory|View
    {
        return view('panel-organization::livewire.job-generation-overlay');
    }

    /**
     * @return array<string, string>
     */
    protected function getListeners(): array
    {
        $userId = auth()->id();

        return [
            'redirect-after-delay' => 'redirectToEdit',
            sprintf('echo-private:job-requisition.generation.%s,.processing', $userId) => 'onProcessing',
            sprintf('echo-private:job-requisition.generation.%s,.success', $userId) => 'onSuccess',
            sprintf('echo-private:job-requisition.generation.%s,.error', $userId) => 'onError',
        ];
    }
}
