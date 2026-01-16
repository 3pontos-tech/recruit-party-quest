<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums\Session;

enum AiEmotionalState: string
{
    case Engaged = 'engaged';
    case Neutral = 'neutral';
    case Playful = 'playful';
    case Disengaged = 'disengaged';
    case Mocking = 'mocking';
    case Disrespectful = 'disrespectful';

    public function respectLevel(): float
    {
        return match ($this) {
            self::Engaged => 1.0,
            self::Neutral => 1.0,
            self::Playful => 1.0,
            self::Disengaged => 0.9,
            self::Mocking => 0.7,
            self::Disrespectful => 0.0,
        };
    }

    public function confidenceRange(): array
    {
        return match ($this) {
            self::Engaged => [0.9, 1.0],
            self::Neutral => [0.6, 0.8],
            self::Playful => [0.7, 0.9],
            self::Disengaged => [0.4, 0.6],
            self::Mocking => [0.3, 0.5],
            self::Disrespectful => [1.0, 1.0],
        };
    }

    public function action(): string
    {
        return match ($this) {
            self::Engaged => 'Continue a conversa normalmente.',
            self::Neutral => 'Manter leveza e ritmo normal.',
            self::Playful => 'Entrar no clima com moderação.',
            self::Disengaged => 'Reengajar suavemente e simplificar perguntas.',
            self::Mocking => 'Levar com leveza e recentrar a conversa.',
            self::Disrespectful => 'Encerrar a conversa imediatamente com gentileza.',
        };
    }
}
