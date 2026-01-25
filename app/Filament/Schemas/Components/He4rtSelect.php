<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Concerns\HasDescription;
use Filament\Support\Concerns\HasIcon;
use Filament\Support\Concerns\HasIconColor;

class He4rtSelect extends Select
{
    use HasDescription;
    use HasIcon;
    use HasIconColor;

    protected string $view = 'filament.schemas.components.he4rt-select';

    protected function setUp(): void
    {
        parent::setUp();
    }
}
