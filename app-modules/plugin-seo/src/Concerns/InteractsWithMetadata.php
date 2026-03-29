<?php

declare(strict_types=1);

namespace He4rt\PluginSeo\Concerns;

use Exception;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use He4rt\PluginSeo\Contracts\HasMetadata;
use He4rt\PluginSeo\Metadata;
use He4rt\PluginSeo\SeoPlugin;

trait InteractsWithMetadata
{
    public function mountInteractsWithMetadata(): void
    {
        if (! $this instanceof HasMetadata) {
            return;
        }

        $metadata = $this->getMetadata();

        // Merge with plugin panel defaults (if SeoPlugin registered)
        $plugin = $this->getSeoPlugin();

        if ($plugin !== null) {
            $metadata = $metadata->mergeWith($plugin->getDefaults());
        }

        // Merge with global config defaults (lowest priority)
        $metadata = $metadata->mergeWith(Metadata::fromConfig());

        // Force noindex on non-production environments
        if (! app()->isProduction()) {
            $metadata->robots('noindex, nofollow');
        }

        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => view('plugin-seo::metatags', $metadata->toArray())->render(),
        );
    }

    protected function getSeoPlugin(): ?SeoPlugin
    {
        try {
            return Filament::getCurrentPanel()?->getPlugin('seo');
        } catch (Exception) {
            return null;
        }
    }
}
