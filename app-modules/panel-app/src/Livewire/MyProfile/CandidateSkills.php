<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use He4rt\Candidates\Models\Skill;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class CandidateSkills extends MyProfileComponent
{
    public ?array $data = [];

    public static $sort = 40;
    protected string $view = 'panel-app::livewire.my-profile.candidate-skills';

    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'skills' => $candidate->skills->map(fn (Skill $skill) => [
                'skill_id' => $skill->id,
                'years_of_experience' => $skill->pivot->years_of_experience,
                'proficiency_level' => $skill->pivot->proficiency_level,
            ])->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('skills')
                    ->label(__('panel-app::pages/settings.skills.fields.skills'))
                    ->schema([
                        Select::make('skill_id')
                            ->label(__('panel-app::pages/settings.skills.fields.skill'))
                            ->options(fn () => Skill::query()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->required(),
                        TextInput::make('years_of_experience')
                            ->label(__('panel-app::pages/settings.skills.fields.years_of_experience'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->required(),
                        Select::make('proficiency_level')
                            ->label(__('panel-app::pages/settings.skills.fields.proficiency_level'))
                            ->options(__('panel-app::pages/settings.skills.options.proficiency_levels'))
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $candidate = auth()->user()->candidate;

        $syncData = [];
        foreach ($data['skills'] as $entry) {
            $syncData[$entry['skill_id']] = [
                'years_of_experience' => $entry['years_of_experience'],
                'proficiency_level' => $entry['proficiency_level'],
            ];
        }

        $candidate->skills()->sync($syncData);

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.skills.notify'))
            ->send();
    }
}
