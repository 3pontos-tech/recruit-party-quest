<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;
use Prism\Prism\Enums\Provider;

enum AiModel: string implements HasLabel
{
    case OpenAiGpt4oMini = 'gpt-5-mini';

    case Test = 'test';

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): ?string
    {

        return match ($this) {
            self::OpenAiGpt4oMini => 'Gp4oMini',
            self::Test => 'Test',
        };
    }

    /**
     * @return array<AiModelApplicabilityFeature>
     */
    public function getApplicableFeatures(): array
    {
        $features = match ($this) {
            self::OpenAiGpt4oMini => [],
            self::Test => app()->hasDebugModeEnabled() ? AiModelApplicabilityFeature::cases() : [],
        };

        return array_map(AiModelApplicabilityFeature::parse(...), $features);
    }

    public function getProvider(): ?Provider
    {
        return match ($this) {
            self::OpenAiGpt4oMini => Provider::OpenAI,
            self::Test => null,
        };
    }
}
