<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(fn () => auth()->user()->candidate->loadMissing('media'))
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('panel-app::pages/settings.profile_info.fields.avatar'))
                    ->collection('avatar')
                    ->visibility('public')
                    ->avatar()
                    ->image()
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->columnSpanFull(),
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
                PhoneInput::make('phone_number')
                    ->label(__('panel-app::pages/settings.profile_info.fields.phone_number'))
                    ->defaultCountry('BR')
                    ->initialCountry('BR')
                    ->validateFor(country: 'BR')
                    ->validationMessages([
                        'phone' => __('panel-app::pages/settings.profile_info.validations.phone_number'),
                    ]),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $candidate = auth()->user()->candidate;
        $candidate->update(Arr::except($data, ['avatar']));

        $this->form->model($candidate)->saveRelationships();

        $candidate->refresh();

        $this->form->fill([
            'headline' => $candidate->headline,
            'summary' => $candidate->summary,
            'phone_number' => $candidate->phone_number,
        ]);

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.profile_info.notify'))
            ->send();
    }
}
