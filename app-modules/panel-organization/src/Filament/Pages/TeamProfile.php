<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Pages;

use App\Filament\Schemas\Components\He4rtToggle;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Teams\Team;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @property-read Schema $form
 */
class TeamProfile extends Page
{
    use InteractsWithSchemas;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'team-profile';

    protected string $view = 'panel-organization::pages.team-profile';

    public static function getNavigationIcon(): string|Heroicon|null
    {
        return Heroicon::BuildingOffice2;
    }

    public static function getNavigationLabel(): string
    {
        return __('teams::filament.profile.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('teams::filament.profile.label');
    }

    public function mount(): void
    {
        /** @var Team|null $tenant */
        $tenant = Filament::getTenant();

        $this->form->fill([
            'about' => $tenant?->about,
            'work_schedule' => $tenant?->work_schedule,
            'accessibility_accommodations' => $tenant?->accessibility_accommodations,
            'is_disability_confident' => $tenant?->is_disability_confident,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('teams::filament.profile.sections.about'))
                    ->description(__('teams::filament.profile.sections.about_description'))
                    ->icon(Heroicon::BuildingOffice2)
                    ->schema([
                        Textarea::make('about')
                            ->label(__('teams::filament.profile.fields.about'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('teams::filament.profile.sections.work_environment'))
                    ->description(__('teams::filament.profile.sections.work_environment_description'))
                    ->icon(Heroicon::Clock)
                    ->schema([
                        Textarea::make('work_schedule')
                            ->label(__('teams::filament.profile.fields.work_schedule'))
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('accessibility_accommodations')
                            ->label(__('teams::filament.profile.fields.accessibility_accommodations'))
                            ->rows(4)
                            ->columnSpanFull(),
                        He4rtToggle::make('is_disability_confident')
                            ->label(__('teams::filament.profile.fields.is_disability_confident'))
                            ->default(false),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var Team|null $tenant */
        $tenant = Filament::getTenant();
        $tenant?->update($data);

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
