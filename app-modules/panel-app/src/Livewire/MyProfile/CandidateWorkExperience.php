<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use He4rt\Candidates\Models\WorkExperience;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

/**
 * @property mixed $form
 */
class CandidateWorkExperience extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
    public static $sort = 35;

    protected string $view = 'panel-app::livewire.my-profile.candidate-work-experience';

    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'work_experiences' => $candidate->workExperiences->map(fn (WorkExperience $experience) => [
                'id' => $experience->id,
                'company_name' => $experience->company_name,
                'description' => $experience->description,
                'start_date' => $experience->start_date,
                'end_date' => $experience->end_date,
                'is_currently_working_here' => $experience->is_currently_working_here,
            ])->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('work_experiences')
                    ->label(__('panel-app::pages/settings.work_experience.fields.work_experiences'))
                    ->schema([
                        TextInput::make('company_name')
                            ->label(__('panel-app::pages/settings.work_experience.fields.company_name'))
                            ->prefixIcon('heroicon-o-building-office')
                            ->placeholder(__('panel-app::pages/settings.work_experience.placeholders.company_name'))
                            ->maxLength(255)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('panel-app::pages/settings.work_experience.fields.description'))
                            ->placeholder(__('panel-app::pages/settings.work_experience.placeholders.description'))
                            ->rows(4)
                            ->maxLength(1000)
                            ->required()
                            ->columnSpanFull(),
                        DatePicker::make('start_date')
                            ->label(__('panel-app::pages/settings.work_experience.fields.start_date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false)
                            ->displayFormat('M Y')
                            ->format('Y-m-d')
                            ->maxDate(now())
                            ->required(),
                        DatePicker::make('end_date')
                            ->label(__('panel-app::pages/settings.work_experience.fields.end_date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false)
                            ->displayFormat('M Y')
                            ->format('Y-m-d')
                            ->maxDate(now())
                            ->required(fn (Get $get) => $get('is_currently_working_here') === false)
                            ->hidden(fn (Get $get) => $get('is_currently_working_here') === true),
                        Toggle::make('is_currently_working_here')
                            ->label(__('panel-app::pages/settings.work_experience.fields.is_currently_working_here'))
                            ->inline(false)
                            ->live(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['company_name'] ?? null)
                    ->addActionLabel(__('panel-app::pages/settings.work_experience.add_work_experience'))
                    ->reorderable()
                    ->collapsible()
                    ->cloneable()
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $candidate = auth()->user()->candidate;

        $existingIds = [];

        foreach ($data['work_experiences'] as $entry) {
            if (filled($entry['id'] ?? null)) {
                $candidate->workExperiences()->where('id', $entry['id'])->update([
                    'company_name' => $entry['company_name'],
                    'description' => $entry['description'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'] ?? null,
                    'is_currently_working_here' => $entry['is_currently_working_here'] ?? false,
                ]);
                $existingIds[] = $entry['id'];
            } else {
                $experience = $candidate->workExperiences()->create([
                    'company_name' => $entry['company_name'],
                    'description' => $entry['description'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'] ?? null,
                    'is_currently_working_here' => $entry['is_currently_working_here'] ?? false,
                ]);
                $existingIds[] = $experience->id;
            }
        }

        $candidate->workExperiences()->whereNotIn('id', $existingIds)->delete();

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.work_experience.notify'))
            ->send();
    }
}
