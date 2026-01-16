<?php

declare(strict_types=1);

namespace He4rt\Ai;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class AiPlugin implements Plugin
{
    public function getId(): string
    {
        return 'ai';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'He4rt\\Ai\\Filament\\Resources'
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'He4rt\\Ai\\Filament\\Pages'
            );
    }

    public function boot(Panel $panel): void {}
}
