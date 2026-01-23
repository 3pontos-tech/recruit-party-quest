<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Jobs\AiAnalyzeResumeJob;
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

    private function uploadHooks(Get $get, OnboardingWizard $livewire): void
    {
        /** @var TemporaryUploadedFile $temporaryFile */
        $temporaryFile = $get('cv_file');

        /** @phpstan-ignore-next-line identical.alwaysFalse */
        if (is_null($temporaryFile) === null) {
            return;
        }

        $livewire->canSkipResumeAnalysis = false;

        dispatch(new AiAnalyzeResumeJob($temporaryFile->getFilename(), auth()->user()->getKey()));

        Notification::make()
            ->title(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_uploading'))
            ->info()
            ->send();
    }
}
