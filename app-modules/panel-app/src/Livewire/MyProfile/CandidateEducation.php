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

/**
 * @property \Filament\Forms\Form $form
 */
class CandidateEducation extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
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
                            ->prefixIcon('heroicon-o-building-library')
                            ->placeholder(__('panel-app::pages/settings.education.placeholders.institution'))
                            ->maxLength(255)
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('degree')
                            ->label(__('panel-app::pages/settings.education.fields.degree'))
                            ->prefixIcon('heroicon-o-academic-cap')
                            ->placeholder(__('panel-app::pages/settings.education.placeholders.degree'))
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('field_of_study')
                            ->label(__('panel-app::pages/settings.education.fields.field_of_study'))
                            ->prefixIcon('heroicon-o-book-open')
                            ->placeholder(__('panel-app::pages/settings.education.placeholders.field_of_study'))
                            ->maxLength(255)
                            ->required(),
                        DatePicker::make('start_date')
                            ->label(__('panel-app::pages/settings.education.fields.start_date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false)
                            ->displayFormat('M Y')
                            ->format('Y-m-d')
                            ->maxDate(now())
                            ->required(),
                        DatePicker::make('end_date')
                            ->label(__('panel-app::pages/settings.education.fields.end_date'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->native(false)
                            ->displayFormat('M Y')
                            ->format('Y-m-d')
                            ->maxDate(now()->addYears(10))
                            ->required(fn (Get $get) => $get('is_enrolled') === false)
                            ->hidden(fn (Get $get) => $get('is_enrolled') === true),
                        Toggle::make('is_enrolled')
                            ->label(__('panel-app::pages/settings.education.fields.is_enrolled'))
                            ->inline(false)
                            ->live(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['institution'] ?? null)
                    ->addActionLabel(__('panel-app::pages/settings.education.add_education'))
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
