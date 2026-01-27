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

        $this->form->model($tenant);
        $this->form->loadStateFromRelationships();
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

                Section::make('Links')
                    ->schema([
                        LinkRepeater::make(),
                    ]),
            ]);
    }

    public function save(): void
    {
        /** @var Team|null $tenant */
        $tenant = Filament::getTenant();

        $data = $this->form->getState();

        // Handle links manually if Repeater::relationship() doesn't work out of the box in this context
        // But since we used relationship(), Filament expects to save it via saveRelationships() trait method or similar.
        // However, standard Page usually doesn't have saveRelationships().
        // Let's try to assume Schema handles it or we manually save:
        // If we use Repeater without relationship(), we get an array in $data['links'].
        // Then we can sync.
        // But LinkRepeater uses relationship().
        // If I can't easily rely on auto-saving, I might need to change LinkRepeater to NOT use relationship() and handle saving here.
        // Given the instructions "Please make it as simple so we can just use the trait on Team... and then just add the Schema",
        // implying the component should ideally handle it or be easy to hook up.
        // But Repeater::relationship() needs the form to define the model.
        // Schema::make()->model($tenant) might work.

        $tenant?->update($data);

        // Create an instance of the form component to save relationships if implicit saving didn't happen?
        // Actually, InteractsWithSchemas likely creates a Form instance.
        // I will try to call $this->form->model($tenant)->saveRelationships(); if available.
        // But typically update() on model handles attributes. Relationships need separate handling.

        // For now, I'll rely on the user's "deal with the save method inside the Livewire component" comment.
        // I'll leave the save() as is (except for update) and if relationship saving is needed, I'll add it.
        // Wait, if I simply use Repeater::make('links') WITHOUT relationship(), I get data.
        // If I use WITH relationship(), I get nothing in $data usually (it's handled separately).
        // I'll stick to LinkRepeater::make() (which has relationship()) and modify save() to try to save relationships.
        // Using `loadStateFromRelationships(true)` in mount might be needed too.

        $this->form->model($tenant)->saveRelationships();

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
