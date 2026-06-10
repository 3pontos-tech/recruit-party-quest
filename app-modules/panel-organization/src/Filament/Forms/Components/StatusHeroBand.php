<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Forms\Components;

use Filament\Forms\Components\ToggleButtons;

/**
 * Status picker rendered as a large "hero band": one segment per status, the
 * selected one expands and fills with the status color while every label stays
 * visible. Extends ToggleButtons so options, enum casting, `live()` and the
 * state path are untouched — only the view changes.
 */
class StatusHeroBand extends ToggleButtons
{
    protected string $view = 'panel-organization::filament.forms.components.status-hero-band';
}
