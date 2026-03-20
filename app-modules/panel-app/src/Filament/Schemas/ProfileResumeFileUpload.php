<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\Candidates\Jobs\AiAnalyzeResumeJob;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProfileResumeFileUpload extends FileUpload
{
    protected string $view = 'panel-app::components.profile.resume-file-upload';

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('panel-app::pages/settings.resume_upload.cv_file_label'))
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(10240)
            ->directory('cv-uploads')
            ->visibility('private')
            ->required()
            ->afterStateUpdated($this->uploadHooks(...))
            ->helperText(__('panel-app::pages/settings.resume_upload.cv_file_helper'));
    }

    private function uploadHooks(Get $get, Component $livewire): void
    {
        /** @var null|TemporaryUploadedFile $temporaryFile */
        $temporaryFile = $get('cv_file');

        if (is_null($temporaryFile)) {
            return;
        }

        dispatch(new AiAnalyzeResumeJob($temporaryFile->getFilename(), auth()->user()->getKey()));

        $livewire->dispatch('queued');

        Notification::make()
            ->title(__('panel-app::pages/settings.resume_upload.notify_uploading'))
            ->info()
            ->send();
    }
}
