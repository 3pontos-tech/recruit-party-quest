<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ResumeFileUploadProgress extends Component
{
    public int $progress = 0;

    public string $status = 'idle';

    // idle, sending, processing, finished
    public bool $visible = false;

    #[On('queued')]
    public function queued(): void
    {
        $this->visible = true;
        $this->status = 'sending';
        $this->progress = 33;
    }

    #[On('processing')]
    public function processing(): void
    {
        $this->status = 'processing';
        $this->progress = 50;

        Notification::make()
            ->info()
            ->title('Upload foi feito, aguarde enquanto estamos preenchendo suas informações no cadastro!!!!')
            ->send();
    }

    #[On('finished')]
    public function finished(): void
    {
        $this->status = 'finished';
        $this->progress = 100;
    }

    public function render(): View
    {
        return view('panel-app::livewire.onboarding.resume-file-upload-progress');
    }
}
