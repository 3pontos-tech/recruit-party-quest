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
    public static $sort = 30;

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
                    ->prefixIcon('heroicon-o-user-circle')
                    ->placeholder(__('panel-app::pages/settings.profile_info.placeholders.headline'))
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('summary')
                    ->label(__('panel-app::pages/settings.profile_info.fields.summary'))
                    ->placeholder(__('panel-app::pages/settings.profile_info.placeholders.summary'))
                    ->rows(5)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                TextInput::make('phone_number')
                    ->label(__('panel-app::pages/settings.profile_info.fields.phone_number'))
                    ->prefixIcon('heroicon-o-phone')
                    ->tel()
                    ->mask('(99) 99999-9999')
                    ->placeholder(__('panel-app::pages/settings.profile_info.placeholders.phone_number'))
                    ->maxLength(20),
                TextInput::make('linkedin_url')
                    ->label(__('panel-app::pages/settings.profile_info.fields.linkedin_url'))
                    ->prefixIcon('fab-linkedin')
                    ->placeholder('Ex: https://linkedin.com/in/danielhe4rt')
                    ->url()
                    ->maxLength(255),
                TextInput::make('portfolio_url')
                    ->label(__('panel-app::pages/settings.profile_info.fields.portfolio_url'))
                    ->prefixIcon('heroicon-o-link')
                    ->hint('Behance, GitHub, Site Pessoal etc.')
                    ->placeholder('Ex: https://github.com/danielhe4rt')
                    ->url()
                    ->maxLength(255),
            ])
            ->columns(2)
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
