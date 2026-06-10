<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Forms\Components;

use Filament\Forms\Components\ToggleButtons;

/**
 * 1–5 score field rendered as a cumulative dot meter (●●●○○) instead of a row
 * of loose buttons. Extends ToggleButtons so the option binding, required rule
 * and state path are untouched — only the view changes.
 */
class ScoreMeter extends ToggleButtons
{
    protected string $view = 'panel-organization::filament.forms.components.score-meter';
}
