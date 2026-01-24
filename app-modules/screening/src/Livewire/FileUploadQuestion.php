<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\QuestionTypes\Settings\FileUploadSettings;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * @property Schema $form
 */
class FileUploadQuestion extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    /** @var array<string, mixed> */
    #[Modelable]
    public ?array $data = ['files' => []];

    public ScreeningQuestion $question;

    public function mount(ScreeningQuestion $question): void
    {
        $this->question = $question;
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $settings = $this->getSettings();

        return $schema
            ->components([
                FileUpload::make('files')
                    ->hiddenLabel()
                    ->multiple($settings->maxFiles > 1)
                    ->maxFiles($settings->maxFiles)
                    ->maxSize($settings->maxSizeKb ?? 5120)
                    ->acceptedFileTypes($settings->getAllowedMimeTypes())
                    ->directory('screening-responses')
                    ->visibility('private')
                    ->required($this->question->is_required)
                    ->afterStateUpdated(function ($data, $get): void {
                        /** @var TemporaryUploadedFile $temporaryFile */
                        $temporaryFile = $get('files');

                        if (blank($temporaryFile)) {
                            return;
                        }

                        $this->data['files'] = $temporaryFile->getFilename();

                        $this->dispatch('file-uploaded', [
                            'questionId' => $this->question->id,
                            'files' => $this->data['files'],
                        ]);
                    })
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->extraAttributes(['class' => 'he4rt-file-upload']),
            ])
            ->statePath('data');
    }

    public function getSettings(): FileUploadSettings
    {
        $settingsArray = $this->question->settings ?? [];

        return FileUploadSettings::fromArray($settingsArray);
    }

    /**
     * @return array<string, mixed>
     */
    public function getUploadedFiles(): array
    {
        return $this->data['files'] ?? [];
    }

    public function render(): View
    {
        return view('screening::livewire.file-upload-question');
    }
}
