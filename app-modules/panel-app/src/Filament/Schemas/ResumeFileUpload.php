<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;

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
            ->extraAttributes([
                //                'x-on:livewire-upload-start' => '$wire.dispatch("processing")',
                'x-on:livewire-upload-start' => "\$wire.dispatch('queued')",

                // Quando o arquivo termina de subir pro servidor
                'x-on:livewire-upload-finish' => "\$wire.dispatch('processing')",
            ])
            ->afterStateUpdated($this->uploadHooks(...))
            ->helperText(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_helper'));
    }

    private function uploadHooks(Get $get, OnboardingWizard $livewire): void
    {
        $get('cv_file');
        /** @var CandidateOnboardingDTO $fields */
        //        $livewire->dispatch('processing');
        //        $fields = resolve(AiAutocompleteInterface::class)->execute($temporaryFile);
        //        $livewire->fillFields($fields, $temporaryFile);
        $livewire->dispatch('finished');

        // Simulate processing after a short delay or async
        // For now just dispatching the next ones to show it works
        // Ideally these would be dispatched by background jobs or AI processing events

        Notification::make()
            ->title(__('panel-app::pages/onboarding.steps.cv.fields.cv_file'))
            ->send();
    }
}
