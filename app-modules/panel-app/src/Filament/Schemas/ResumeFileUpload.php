<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ResumeFileUpload extends FileUpload
{
    protected string $view = 'panel-app::components.onboarding.resume-file-upload';
    protected function setUp(): void
    {
        $this->label(__('panel-app::pages/onboarding.steps.cv.fields.cv_file'))
            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
            ->maxSize(10240)
            ->directory('cv-uploads')
            ->visibility('private')
            ->required()
            ->afterStateUpdated($this->uploadHooks(...))
            ->helperText(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_helper'));
    }

    private function uploadHooks(TemporaryUploadedFile $state): void
    {
        Notification::make()
            ->title(__('panel-app::pages/onboarding.steps.cv.fields.cv_file'))
            ->send();
    }
}
