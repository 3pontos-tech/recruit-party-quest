<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Concerns\HasDescription;
use Filament\Support\Concerns\HasIcon;

class He4rtToggle extends Toggle
{
    use HasDescription;
    use HasIcon;

    protected string $view = 'filament.schemas.components.he4rt-toggle';

}
