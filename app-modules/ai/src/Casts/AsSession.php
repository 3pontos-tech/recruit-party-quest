<?php

declare(strict_types=1);

namespace He4rt\Ai\Casts;

use He4rt\Ai\Entities\ChatSession;
use He4rt\Ai\Enums\Session\AiActionState;
use He4rt\Ai\Enums\Session\AiEmotionalState;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use ValueError;

final class AsSession implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ChatSession
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Value may already be an array if using database drivers that decode JSON automatically
        $data = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($data)) {
            return null;
        }

        // Normalize and instantiate entity
        $state = $data['emotionalState'] ?? $data['emotional_state'] ?? null;
        $confidence = (float) ($data['confidence'] ?? 0.0);
        $respectLevel = (float) ($data['respectLevel'] ?? $data['respect_level'] ?? 0.0);
        $reason = (string) ($data['reason'] ?? '');
        $action = (string) ($data['action'] ?? '');

        if (is_string($state)) {
            try {
                $stateEnum = AiEmotionalState::from($state);
            } catch (ValueError) {
                // Fallback to Neutral if an invalid value is stored
                $stateEnum = AiEmotionalState::Neutral;
            }
        } elseif ($state instanceof AiEmotionalState) {
            $stateEnum = $state;
        } else {
            $stateEnum = AiEmotionalState::Neutral;
        }

        return new ChatSession(
            emotionalState: $stateEnum,
            confidence: $confidence,
            respectLevel: $respectLevel,
            reason: $reason,
            action: AiActionState::from($action),
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ChatSession) {
            $payload = [
                'emotional_state' => $value->emotionalState->value,
                'confidence' => $value->confidence,
                'respect_level' => $value->respectLevel,
                'reason' => $value->reason,
                'action' => $value->action,
            ];
        } elseif (is_array($value)) {
            $payload = $value;

            // Normalize enum to string value when provided as enum instance
            if (($payload['emotionalState'] ?? null) instanceof AiEmotionalState) {
                $payload['emotionalState'] = $payload['emotionalState']->value;
            }

            if (($payload['emotional_state'] ?? null) instanceof AiEmotionalState) {
                $payload['emotional_state'] = $payload['emotional_state']->value;
            }

            // Normalize to snake_case keys for storage
            if (isset($payload['emotionalState']) && ! isset($payload['emotional_state'])) {
                $payload['emotional_state'] = $payload['emotionalState'];
                unset($payload['emotionalState']);
            }

            if (isset($payload['respectLevel']) && ! isset($payload['respect_level'])) {
                $payload['respect_level'] = $payload['respectLevel'];
                unset($payload['respectLevel']);
            }
        } else {
            return $value;
        }

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
