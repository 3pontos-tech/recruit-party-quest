<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiMessageLogFeature: string implements HasLabel
{
    case DraftWithAi = 'draft_with_ai';

    case Conversations = 'conversations';

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): string
    {
        return __('ai::filament.enums.message_log_feature.'.$this->value.'.label');
    }
}
