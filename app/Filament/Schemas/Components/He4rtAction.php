<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Illuminate\View\ComponentAttributeBag;

class He4rtAction extends Action
{
    protected string|Closure $variant = 'solid';

    protected string|Closure $rounded = 'md';

    public function variant(string|Closure $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    public function getVariant(): string
    {
        return $this->evaluate($this->variant);
    }

    public function rounded(string|Closure $rounded): static
    {
        $this->rounded = $rounded;

        return $this;
    }

    public function getRounded(): string
    {
        return $this->evaluate($this->rounded);
    }

    protected function toButtonHtml(): string
    {
        $isDisabled = $this->isDisabled();
        $url = $this->getUrl();
        $shouldPostToUrl = $this->shouldPostToUrl();

        return view('filament.schemas.components.he4rt-action.button', [
            'action' => $this,
            'attributes' => new ComponentAttributeBag([
                'action' => $shouldPostToUrl ? $url : null,
                'method' => $shouldPostToUrl ? 'post' : null,
                'wire:click' => $this->getLivewireClickHandler(),
                'wire:target' => $this->getLivewireTarget(),
                'x-on:click' => $this->getAlpineClickHandler(),
            ])
                ->merge($this->getExtraAttributes(), escape: false)
                ->class(['fi-ac-btn-action', 'he4rt-action']),
            'badge' => $this->getBadge(),
            'badgeColor' => $this->getBadgeColor(),
            'color' => $this->getColor(),
            'form' => $this->getFormToSubmit(),
            'formId' => $this->getFormId(),
            'href' => ($isDisabled || $shouldPostToUrl) ? null : $url,
            'icon' => $this->getIcon(default: $this->getTable() instanceof Table ? $this->getTableIcon() : null),
            'iconPosition' => $this->getIconPosition(),
            'iconSize' => $this->getIconSize(),
            'disabled' => $isDisabled,
            'labelSrOnly' => $this->isLabelHidden(),
            'outlined' => $this->isOutlined(),
            'keyBindings' => $this->getKeyBindings(),
            'label' => $this->getLabel(),
            'labeledFrom' => $this->getLabeledFromBreakpoint(),
            'size' => $this->getSize(),
            'tag' => $url ? ($shouldPostToUrl ? 'form' : 'a') : 'button',
            'target' => ($url && $this->shouldOpenUrlInNewTab()) ? '_blank' : null,
            'tooltip' => $this->getTooltip(),
            'type' => $this->canSubmitForm() ? 'submit' : 'button',
            'variant' => $this->getVariant(),
            'rounded' => $this->getRounded(),
        ])->render();
    }

    protected function toIconButtonHtml(): string
    {
        $isDisabled = $this->isDisabled();
        $url = $this->getUrl();
        $shouldPostToUrl = $this->shouldPostToUrl();

        return view('filament.schemas.components.he4rt-action.icon-button', [
            'action' => $this,
            'attributes' => new ComponentAttributeBag([
                'action' => $shouldPostToUrl ? $url : null,
                'method' => $shouldPostToUrl ? 'post' : null,
                'wire:click' => $this->getLivewireClickHandler(),
                'wire:target' => $this->getLivewireTarget(),
                'x-on:click' => $this->getAlpineClickHandler(),
            ])
                ->merge($this->getExtraAttributes(), escape: false)
                ->class(['fi-ac-icon-btn-action', 'he4rt-action']),
            'badge' => $this->getBadge(),
            'badgeColor' => $this->getBadgeColor(),
            'color' => $this->getColor(),
            'form' => $this->getFormToSubmit(),
            'formId' => $this->getFormId(),
            'href' => ($isDisabled || $shouldPostToUrl) ? null : $url,
            'icon' => $this->getIcon(default: $this->getTable() instanceof Table ? $this->getTableIcon() : null),
            'iconSize' => $this->getIconSize(),
            'disabled' => $isDisabled,
            'keyBindings' => $this->getKeyBindings(),
            'label' => $this->getLabel(),
            'size' => $this->getSize(),
            'tag' => $url ? ($shouldPostToUrl ? 'form' : 'a') : 'button',
            'target' => ($url && $this->shouldOpenUrlInNewTab()) ? '_blank' : null,
            'tooltip' => $this->getTooltip(),
            'type' => $this->canSubmitForm() ? 'submit' : 'button',
            'variant' => $this->getVariant(),
            'rounded' => $this->getRounded(),
        ])->render();
    }

    protected function toLinkHtml(): string
    {
        $isDisabled = $this->isDisabled();
        $url = $this->getUrl();
        $shouldPostToUrl = $this->shouldPostToUrl();

        return view('filament.schemas.components.he4rt-action.link', [
            'action' => $this,
            'attributes' => new ComponentAttributeBag([
                'action' => $shouldPostToUrl ? $url : null,
                'method' => $shouldPostToUrl ? 'post' : null,
                'wire:click' => $this->getLivewireClickHandler(),
                'wire:target' => $this->getLivewireTarget(),
                'x-on:click' => $this->getAlpineClickHandler(),
            ])
                ->merge($this->getExtraAttributes(), escape: false)
                ->class(['fi-ac-link-action', 'he4rt-action']),
            'badge' => $this->getBadge(),
            'badgeColor' => $this->getBadgeColor(),
            'color' => $this->getColor(),
            'href' => ($isDisabled || $shouldPostToUrl) ? null : $url,
            'icon' => $this->getIcon(default: $this->getTable() instanceof Table ? $this->getTableIcon() : null),
            'iconPosition' => $this->getIconPosition(),
            'iconSize' => $this->getIconSize(),
            'disabled' => $isDisabled,
            'labelSrOnly' => $this->isLabelHidden(),
            'keyBindings' => $this->getKeyBindings(),
            'label' => $this->getLabel(),
            'size' => $this->getSize(),
            'tag' => $url ? ($shouldPostToUrl ? 'form' : 'a') : 'button',
            'target' => ($url && $this->shouldOpenUrlInNewTab()) ? '_blank' : null,
            'tooltip' => $this->getTooltip(),
            'type' => $this->canSubmitForm() ? 'submit' : 'button',
            'variant' => $this->getVariant(),
        ])->render();
    }
}
