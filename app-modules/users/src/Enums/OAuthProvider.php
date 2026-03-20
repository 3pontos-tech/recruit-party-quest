<?php

declare(strict_types=1);

namespace He4rt\Users\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OAuthProvider: string implements HasColor, HasIcon, HasLabel
{
    case GitHub = 'github';
    case Google = 'google';
    case LinkedIn = 'linkedin-openid';

    public function getLabel(): string
    {
        return __('users::enums.oauth_provider.'.$this->value.'.label');
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::GitHub => 'fab-github',
            self::Google => 'fab-google',
            self::LinkedIn => 'fab-linkedin',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::GitHub => 'gray',
            self::Google => 'danger',
            self::LinkedIn => 'info',
        };
    }
}
