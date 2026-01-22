<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiThreadShareTarget: string implements HasLabel
{
    case User = 'user';
    case Team = 'team';

    public static function default(): AiThreadShareTarget
    {
        return AiThreadShareTarget::User;
    }

    public static function parse(string|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::from($value);
    }

    public function getLabel(): string
    {
        return __('ai::filament.enums.thread_share_target.'.$this->value.'.label');
    }
}
