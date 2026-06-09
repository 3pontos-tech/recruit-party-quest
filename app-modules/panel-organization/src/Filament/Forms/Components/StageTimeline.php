<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Radio;

/**
 * Single-choice field rendered as a horizontal funnel timeline instead of a
 * dropdown. Extends Radio so it keeps native option binding, validation and
 * visibility — only the view changes. The current stage is shown as a passed
 * anchor (✓) ahead of the selectable downstream stages.
 */
class StageTimeline extends Radio
{
    protected string $view = 'panel-organization::filament.forms.components.stage-timeline';

    protected string|Closure|null $fromStageLabel = null;

    public function fromStageLabel(string|Closure|null $label): static
    {
        $this->fromStageLabel = $label;

        return $this;
    }

    public function getFromStageLabel(): ?string
    {
        return $this->evaluate($this->fromStageLabel);
    }
}
