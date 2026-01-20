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
use Livewire\Component;

class FileUploadQuestion extends Component implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public ?array $data = [];

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
