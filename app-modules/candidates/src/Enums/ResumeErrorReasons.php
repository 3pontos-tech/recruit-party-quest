<?php

declare(strict_types=1);

namespace He4rt\Candidates\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResumeErrorReasons: string implements HasLabel
{
    case NotAnCV = 'not-an-cv';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotAnCV => 'Arquivo enviado não é um currículo.',
        };
    }
}
