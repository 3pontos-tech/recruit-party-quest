<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Schemas\Components\Wizard;
use Filament\Support\Enums\IconPosition;

class He4rtWizard extends Wizard
{
    protected string $view = 'filament.schemas.components.he4rt-wizard';

    public function getNextAction(): Action
    {
        $action = He4rtAction::make($this->getNextActionName())
            ->label(__('filament-schemas::components.wizard.actions.next_step.label'))
            ->icon('heroicon-o-arrow-right')
            ->iconSize('sm')
            ->iconPosition(IconPosition::After)
            ->livewireClickHandlerEnabled(false)
            ->livewireTarget('callSchemaComponentMethod')
            ->button();

        if ($this->modifyNextActionUsing instanceof Closure) {
            return $this->evaluate($this->modifyNextActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action;
    }
}
