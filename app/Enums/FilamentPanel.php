<?php

declare(strict_types=1);

namespace App\Enums;

enum FilamentPanel: string
{
    case Admin = 'admin';

    case App = 'app';
    case Organization = 'organization';

    public function getPath(): string
    {
        return match ($this) {
            self::Admin => 'admin',
            self::App => '',
            self::Organization => 'organization',
        };

    }
}
