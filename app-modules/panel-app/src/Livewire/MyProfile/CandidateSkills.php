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

/**
 * @property mixed $form
 */
class CandidateSkills extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
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
                            ->prefixIcon('heroicon-o-code-bracket')
                            ->options(fn () => Skill::query()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->placeholder(__('panel-app::pages/settings.skills.placeholders.skill'))
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('years_of_experience')
                            ->label(__('panel-app::pages/settings.skills.fields.years_of_experience'))
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->suffix(__('panel-app::pages/settings.skills.placeholders.years_suffix'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(50)
                            ->placeholder('0')
                            ->required(),
                        Select::make('proficiency_level')
                            ->label(__('panel-app::pages/settings.skills.fields.proficiency_level'))
                            ->prefixIcon('heroicon-o-star')
                            ->options(__('panel-app::pages/settings.skills.options.proficiency_levels'))
                            ->placeholder(__('panel-app::pages/settings.skills.placeholders.proficiency_level'))
                            ->required(),
                    ])
                    ->itemLabel(function (array $state): ?string {
                        if (! isset($state['skill_id'])) {
                            return null;
                        }

                        $skill = Skill::query()->find($state['skill_id']);

                        return $skill?->name;
                    })
                    ->addActionLabel(__('panel-app::pages/settings.skills.add_skill'))
                    ->reorderable()
                    ->collapsible()
                    ->cloneable()
                    ->columns(4)
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
