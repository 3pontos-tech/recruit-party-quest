<?php

declare(strict_types=1);

namespace He4rt\Ai\Schema;

use He4rt\Ai\Enums\Session\AiActionState;
use He4rt\Ai\Enums\Session\AiEmotionalState;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

final class AiSessionSchema
{
    /**
     * Session schema of each interaction with the assistant
     *
     * Result of one interaction analysis: emotional state, confidence, respect, reason, action
     */
    public static function make(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'session',
            description: 'Result of one interaction analysis: emotional state, confidence, respect, reason, action',
            properties: [
                new EnumSchema(
                    name: 'emotional_state',
                    description: 'One of the engagement/emotional states',
                    options: array_map(fn (AiEmotionalState $action) => $action->value, AiEmotionalState::cases())
                ),
                new NumberSchema('confidence', 'Confidence level (0.0-1.0)'),
                new NumberSchema('respect_level', 'Respect level (0.0-1.0)'),
                new StringSchema('reason', 'Reason or justification for the classification'),
                new BooleanSchema('valid_response', 'Whether the actual user response was valid or not'),
                new EnumSchema(
                    name: 'action',
                    description: 'Recommended next action by the assistant',
                    options: array_map(fn (AiActionState $action) => $action->value, AiActionState::cases())
                ),
                new StringSchema('assistant_response', 'Final assistant response text'),
            ],
            requiredFields: [
                'emotional_state',
                'confidence',
                'respect_level',
                'reason',
                'valid_response',
                'action',
                'assistant_response',
            ]
        );
    }
}
