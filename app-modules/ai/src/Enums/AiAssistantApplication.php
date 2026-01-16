<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;
use He4rt\Ai\Settings\AiSettings;

enum AiAssistantApplication: string implements HasLabel
{
    case PersonalAssistant = 'personal_assistant';

    case Test = 'test';

    public static function getDefault(): self
    {
        return self::PersonalAssistant;
    }

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PersonalAssistant => 'Personal Assistant',
            self::Test => 'Test',
        };
    }

    public function getDefaultModel(): AiModel
    {
        $settings = resolve(AiSettings::class);

        return match ($this) {
            self::PersonalAssistant => $settings->default_model ?? AiModel::OpenAiGpt4oMini,
            self::Test => AiModel::Test,
        };
    }
}
