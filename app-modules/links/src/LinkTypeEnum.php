<?php

declare(strict_types=1);

namespace He4rt\Links;

use Filament\Support\Contracts\HasLabel;

enum LinkTypeEnum: string implements HasLabel
{
    case LinkedIn = 'linkedin';
    case GitHub = 'github';
    case Instagram = 'instagram';
    case Twitter = 'twitter';
    case YouTube = 'youtube';
    case Behance = 'behance';
    case Dribbble = 'dribbble';
    case Website = 'website';
    case Other = 'other';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::LinkedIn => 'LinkedIn',
            self::GitHub => 'GitHub',
            self::Instagram => 'Instagram',
            self::Twitter => 'X (Twitter)',
            self::YouTube => 'YouTube',
            self::Behance => 'Behance',
            self::Dribbble => 'Dribbble',
            self::Website => 'Website',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LinkedIn => 'fab-linkedin',
            self::GitHub => 'fab-github',
            self::Instagram => 'fab-instagram',
            self::Twitter => 'fab-x-twitter',
            self::YouTube => 'fab-youtube',
            self::Behance => 'fab-behance',
            self::Dribbble => 'fab-dribbble',
            self::Website => 'heroicon-o-globe-alt',
            self::Other => 'heroicon-o-link',
        };
    }

    public function urlPlaceholder(): string
    {
        return match ($this) {
            self::LinkedIn => 'https://linkedin.com/in/username',
            self::GitHub => 'https://github.com/username',
            self::Instagram => 'https://instagram.com/username',
            self::Twitter => 'https://x.com/username',
            self::YouTube => 'https://youtube.com/@channel',
            self::Behance => 'https://behance.net/username',
            self::Dribbble => 'https://dribbble.com/username',
            self::Website => 'https://yourwebsite.com',
            self::Other => 'https://',
        };
    }
}
