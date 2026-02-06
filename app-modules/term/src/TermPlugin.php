<?php

declare(strict_types=1);

namespace He4rt\Term;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class TermPlugin implements Plugin
{
    public function getId(): string
    {
        return 'term';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'He4rt\\Term\\Filament\\Resources'
            );
    }

    public function boot(Panel $panel): void {}
}
