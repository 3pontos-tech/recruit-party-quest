<?php

declare(strict_types=1);

namespace He4rt\Ai\Entities;

use He4rt\Ai\Enums\Session\AiActionState;
use He4rt\Ai\Enums\Session\AiEmotionalState;
use JsonSerializable;
use Stringable;

final class ChatSession implements JsonSerializable, Stringable
{
    public function __construct(
        public AiEmotionalState $emotionalState,
        public ?bool $isValid,
        public float $confidence,
        public float $respectLevel,
        public string $reason,
        public AiActionState $action,
        public int $attemptNumber = 0,
        public string $assistantResponse = '',
    ) {}

    public function __toString(): string
    {
        $prompt = <<<'PROMPT'
            ## Assistant Answer
                %s

            ### AI CHAT SESSION
            Emotional State: %s,
            Confidence: %s,
            Respect Level: %s,
            Context: %s,
            Action: %s,
            Last Response: %s
            Attempts: %s
        PROMPT;

        return sprintf(
            $prompt,
            $this->assistantResponse,
            $this->emotionalState->name,
            $this->confidence,
            $this->respectLevel,
            $this->reason,
            $this->action->getGuideline(),
            $this->isValid ? 'Valid' : 'Invalid',
            $this->attemptNumber
        );

    }

    public static function make(array $payload): self
    {
        return new self(
            emotionalState: AiEmotionalState::from($payload['emotional_state']),
            isValid: $payload['valid_response'] ?? true,
            confidence: $payload['confidence'],
            respectLevel: $payload['respect_level'],
            reason: $payload['reason'],
            action: AiActionState::from($payload['action']),
            attemptNumber: $payload['attempt_number'] ?? 0,
            assistantResponse: $payload['assistant_response'] ?? '',
        );
    }

    public static function default(): self
    {
        return new self(
            emotionalState: AiEmotionalState::Neutral,
            isValid: true,
            confidence: 0.5,
            respectLevel: 1,
            reason: 'Iniciando conversa',
            action: AiActionState::Continue,
            attemptNumber: 0,
            assistantResponse: 'Irei iniciar a primeira pergunta da conversa.',
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'emotional_state' => $this->emotionalState->value,
            'confidence' => $this->confidence,
            'respect_level' => $this->respectLevel,
            'reason' => $this->reason,
            'action' => $this->action,
            'attempt_number' => $this->attemptNumber,
            'assistant_response' => $this->assistantResponse ?: '',
        ];
    }
}
