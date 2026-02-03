<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use DateTimeZone;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

/**
 * @property mixed $form
 */
class CandidatePreferences extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
    public static $sort = 25;

    protected string $view = 'panel-app::livewire.my-profile.candidate-preferences';

    public function mount(): void
    {
        $candidate = auth()->user()->candidate;

        $this->form->fill([
            'expected_salary' => $candidate->expected_salary,
            'expected_salary_currency' => $candidate->expected_salary_currency,
            'availability_date' => $candidate->availability_date,
            'willing_to_relocate' => $candidate->willing_to_relocate,
            'is_open_to_remote' => $candidate->is_open_to_remote,
            'experience_level' => $candidate->experience_level,
            'timezone' => $candidate->timezone,
            'preferred_language' => $candidate->preferred_language,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('expected_salary')
                    ->label(__('panel-app::pages/settings.preferences.fields.expected_salary'))
                    ->numeric()
                    ->prefix('$'),
                Select::make('expected_salary_currency')
                    ->label(__('panel-app::pages/settings.preferences.fields.expected_salary_currency'))
                    ->options([
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'BRL' => 'BRL',
                    ])
                    ->default('USD'),
                DatePicker::make('availability_date')
                    ->label(__('panel-app::pages/settings.preferences.fields.availability_date')),
                Toggle::make('willing_to_relocate')
                    ->label(__('panel-app::pages/settings.preferences.fields.willing_to_relocate')),
                Toggle::make('is_open_to_remote')
                    ->label(__('panel-app::pages/settings.preferences.fields.is_open_to_remote')),
                Select::make('experience_level')
                    ->label(__('panel-app::pages/settings.preferences.fields.experience_level'))
                    ->options(__('panel-app::pages/settings.preferences.options.experience_levels')),
                Select::make('timezone')
                    ->label(__('panel-app::pages/settings.preferences.fields.timezone'))
                    ->options(fn () => collect(DateTimeZone::listIdentifiers())
                        ->mapWithKeys(fn ($tz) => [$tz => $tz])
                        ->all())
                    ->searchable(),
                Select::make('preferred_language')
                    ->label(__('panel-app::pages/settings.preferences.fields.preferred_language'))
                    ->options(__('panel-app::pages/settings.preferences.options.languages')),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        auth()->user()->candidate->update($data);

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.preferences.notify'))
            ->send();
    }
}
