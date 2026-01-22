<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Users\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ResumeFileUploadProgress extends Component
{
    public int $progress = 0;

    public string $status = 'idle';

    public ?User $user = null;

    // idle, sending, processing, finished
    public bool $visible = false;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    #[On('queued')]
    #[On('echo-private:candidate-onboarding.resume.{user.id},.queued')]
    public function queued(): void
    {
        $this->visible = true;
        $this->status = 'sending';
        $this->progress = 33;
    }

    #[On('processing')]
    #[On('echo-private:candidate-onboarding.resume.{user.id},.processing')]
    public function processing(): void
    {
        $this->status = 'processing';
        $this->progress = 50;
    }

    #[On('echo-private:candidate-onboarding.resume.{user.id},.finished')]
    public function finished(): void
    {
        $this->status = 'finished';
        $this->progress = 100;
    }

    #[On('echo-private:candidate-onboarding.resume.{user.id},.error')]
    public function error(): void
    {
        $this->status = 'finished';
        $this->progress = 100;
    }

    public function render(): View
    {
        return view('panel-app::livewire.onboarding.resume-file-upload-progress');
    }
}
