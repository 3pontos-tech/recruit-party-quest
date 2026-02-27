<?php

declare(strict_types=1);

namespace He4rt\Links\Filament\Components;

use Closure;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Guava\IconPicker\Forms\Components\IconPicker;
use Guava\IconPicker\Icons\Icon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;

class He4rtIconPicker extends IconPicker
{
    /** @var array<string, string>|Closure */
    protected array|Closure $allowedIcons = [];

    /**
     * @param  array<string, string>|Closure  $icons
     */
    public function allowedIcons(array|Closure $icons): static
    {
        $this->allowedIcons = $icons;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getAllowedIcons(): array
    {
        return $this->evaluate($this->allowedIcons);
    }

    /**
     * @return Collection<int, Icon>
     */
    #[ExposedLivewireMethod]
    #[Renderless]
    public function getIconsJs(?string $set = null): Collection
    {
        $icons = parent::getIconsJs($set);

        $allowed = $this->getAllowedIcons();

        if ($allowed === []) {
            return $icons;
        }

        $allowedIds = array_keys($allowed);

        return $icons->whereIn('id', $allowedIds)->values();
    }
}
