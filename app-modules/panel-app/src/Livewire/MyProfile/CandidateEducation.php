<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use He4rt\Candidates\Models\Education;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class CandidateEducation extends MyProfileComponent
{
    public ?array $data = [];

    public static $sort = 30;

    protected string $view = 'panel-app::livewire.my-profile.candidate-education';

    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'education' => $candidate->degrees->map(fn (Education $education) => [
                'id' => $education->id,
                'institution' => $education->institution,
                'degree' => $education->degree,
                'field_of_study' => $education->field_of_study,
                'start_date' => $education->start_date,
                'end_date' => $education->end_date,
                'is_enrolled' => $education->is_enrolled,
            ])->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('education')
                    ->label(__('panel-app::pages/settings.education.fields.education'))
                    ->schema([
                        TextInput::make('institution')
                            ->label(__('panel-app::pages/settings.education.fields.institution'))
                            ->required(),
                        TextInput::make('degree')
                            ->label(__('panel-app::pages/settings.education.fields.degree'))
                            ->required(),
                        TextInput::make('field_of_study')
                            ->label(__('panel-app::pages/settings.education.fields.field_of_study'))
                            ->required(),
                        DatePicker::make('start_date')
                            ->label(__('panel-app::pages/settings.education.fields.start_date'))
                            ->required(),
                        DatePicker::make('end_date')
                            ->label(__('panel-app::pages/settings.education.fields.end_date'))
                            ->required(fn (Get $get) => $get('is_enrolled') === false),
                        Toggle::make('is_enrolled')
                            ->label(__('panel-app::pages/settings.education.fields.is_enrolled')),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['institution'] ?? null)
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $candidate = auth()->user()->candidate;

        $existingIds = [];

        foreach ($data['education'] as $entry) {
            if (filled($entry['id'])) {
                $candidate->degrees()->where('id', $entry['id'])->update([
                    'institution' => $entry['institution'],
                    'degree' => $entry['degree'],
                    'field_of_study' => $entry['field_of_study'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'is_enrolled' => $entry['is_enrolled'] ?? false,
                ]);
                $existingIds[] = $entry['id'];
            } else {
                $education = $candidate->degrees()->create([
                    'institution' => $entry['institution'],
                    'degree' => $entry['degree'],
                    'field_of_study' => $entry['field_of_study'],
                    'start_date' => $entry['start_date'],
                    'end_date' => $entry['end_date'],
                    'is_enrolled' => $entry['is_enrolled'] ?? false,
                ]);
                $existingIds[] = $education->id;
            }
        }

        $candidate->degrees()->whereNotIn('id', $existingIds)->delete();

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.education.notify'))
            ->send();
    }
}
