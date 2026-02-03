<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

/**
 * @property mixed $form
 */
class CandidateProfileInfo extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
    public static $sort = 15;

    protected string $view = 'panel-app::livewire.my-profile.candidate-profile-info';

    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'headline' => $candidate->headline,
            'summary' => $candidate->summary,
            'phone_number' => $candidate->phone_number,
            'linkedin_url' => $candidate->linkedin_url,
            'portfolio_url' => $candidate->portfolio_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('headline')
                    ->label(__('panel-app::pages/settings.profile_info.fields.headline'))
                    ->maxLength(255),
                Textarea::make('summary')
                    ->label(__('panel-app::pages/settings.profile_info.fields.summary'))
                    ->rows(4)
                    ->maxLength(2000),
                TextInput::make('phone_number')
                    ->label(__('panel-app::pages/settings.profile_info.fields.phone_number'))
                    ->tel()
                    ->maxLength(20),
                TextInput::make('linkedin_url')
                    ->label(__('panel-app::pages/settings.profile_info.fields.linkedin_url'))
                    ->url()
                    ->maxLength(255),
                TextInput::make('portfolio_url')
                    ->label(__('panel-app::pages/settings.profile_info.fields.portfolio_url'))
                    ->url()
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        auth()->user()->candidate->update($data);

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.profile_info.notify'))
            ->send();
    }
}
