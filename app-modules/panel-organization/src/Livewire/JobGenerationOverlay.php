<?php

declare(strict_types=1);

namespace He4rt\Organization\Livewire;

use Filament\Notifications\Notification;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JobGenerationOverlay extends Component
{
    public string $state = 'idle';

    public ?string $errorMessage = null;

    public ?int $jobRequisitionId = null;

    //    #[On('job-generation-queued')]
    public function onQueued(): void
    {
        $this->state = JobGenerationStatus::Queued->value;
        $this->errorMessage = null;

        logger()->info('Overlay state changed to queued', [
            'user_id' => auth()->id(),
            'trigger' => 'local or echo event',
        ]);
    }

    //    #[On('job-generation-processing')]
    //    #[On('echo-private:job-requisition.generation.{user.id},.processing')]
    public function onProcessing(): void
    {
        $this->state = JobGenerationStatus::Processing->value;

        logger()->info('Overlay state changed to processing', [
            'user_id' => auth()->id(),
        ]);
    }

    //    #[On('job-generation-success')]
    public function onSuccess(array $event): void
    {
        $this->state = JobGenerationStatus::Success->value;
        $this->jobRequisitionId = $event['job_requisition_id'];

        // Log for debugging
        logger()->info('Overlay state changed to success', [
            'user_id' => auth()->id(),
            'job_requisition_id' => $this->jobRequisitionId,
        ]);

        // Aguardar 2 segundos antes de redirecionar
        $this->dispatch('redirect-after-delay');
    }

    //    #[On('echo-private:job-requisition.generation.{user.id},.error')]
    //    #[On('job-generation-error')]
    public function onError(array $event): void
    {
        $this->state = JobGenerationStatus::Error->value;
        $this->errorMessage = $event['error_message'] ?? __('recruitment::filament.requisition.job_posting.notifications.failed');

        // Log for debugging
        logger()->error('Overlay state changed to error', [
            'user_id' => auth()->id(),
            'error' => $this->errorMessage,
        ]);

        Notification::make()
            ->danger()
            ->title(__('recruitment::filament.requisition.job_posting.notifications.failed'))
            ->body($this->errorMessage)
            ->persistent()
            ->send();
    }

    public function closeOverlay(): void
    {
        $this->state = 'idle';
        $this->errorMessage = null;
        $this->jobRequisitionId = null;
    }

    //    #[On('redirect-after-delay')]
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

    protected function getListeners(): array
    {
        $userId = auth()->id();

        return [
            'job-generation-queued' => 'onQueued',
            'job-generation-processing' => 'onProcessing',
            'job-generation-success' => 'onSuccess',
            'job-generation-error' => 'onError',
            'redirect-after-delay' => 'redirectToEdit',

            sprintf('echo:job-requisition.generation.%s,.queued', $userId) => 'onQueued',
            sprintf('echo:job-requisition.generation.%s,.processing', $userId) => 'onProcessing',
            sprintf('echo:job-requisition.generation.%s,.success', $userId) => 'onSuccess',
            sprintf('echo:job-requisition.generation.%s,.error', $userId) => 'onError',
        ];
    }
}
