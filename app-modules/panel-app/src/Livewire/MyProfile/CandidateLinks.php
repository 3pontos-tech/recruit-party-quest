<?php

declare(strict_types=1);

namespace He4rt\App\Livewire\MyProfile;

use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use He4rt\Links\Filament\Components\LinkRepeater;
use He4rt\Links\Link;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

/**
 * @property mixed $form
 */
class CandidateLinks extends MyProfileComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var int */
    public static $sort = 30;

    protected string $view = 'panel-app::livewire.my-profile.candidate-links';

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'links' => $user->links->map(fn (Link $link) => [
                'id' => $link->id,
                'name' => $link->name,
                'url' => $link->url,
                'type' => $link->type?->value,
                'icon' => $link->icon,
                'order_column' => $link->order_column,
            ])->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                LinkRepeater::make(useRelationship: false),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $existingIds = [];

        foreach ($data['links'] ?? [] as $index => $entry) {
            $type = $entry['type'];
            $name = $type?->label();
            $icon = $type?->icon();

            if (filled($entry['id'] ?? null)) {
                $link = $user->links()->find((string) $entry['id']);
                if ($link) {
                    $link->update([
                        'name' => $name,
                        'url' => $entry['url'],
                        'type' => $entry['type'] ?? null,
                        'icon' => $icon,
                        'order_column' => $index,
                    ]);
                    $existingIds[] = $entry['id'];
                }
            } else {
                $link = Link::query()->create([
                    'name' => $name,
                    'url' => $entry['url'],
                    'type' => $entry['type'] ?? null,
                    'icon' => $icon,
                    'order_column' => $index,
                ]);
                $user->attachLink($link);
                $existingIds[] = $link->id;
            }
        }

        $linksToRemove = $user->links()->whereNotIn('links.id', $existingIds)->get();
        foreach ($linksToRemove as $link) {
            $user->detachLink($link);
        }

        Notification::make()
            ->success()
            ->title(__('panel-app::pages/settings.links.notify'))
            ->send();
    }
}
