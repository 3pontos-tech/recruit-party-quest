<?php

declare(strict_types=1);

namespace He4rt\PluginSeo;

use Filament\Contracts\Plugin;
use Filament\Panel;

class SeoPlugin implements Plugin
{
    protected Metadata $defaults;

    public function __construct()
    {
        $this->defaults = Metadata::make();
    }

    public static function make(): static
    {
        return resolve(static::class);
    }

    public function getId(): string
    {
        return 'seo';
    }

    public function defaults(Metadata $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function getDefaults(): Metadata
    {
        return $this->defaults;
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}
}
