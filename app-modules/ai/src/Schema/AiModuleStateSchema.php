<?php

declare(strict_types=1);

namespace He4rt\Ai\Schema;

use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

final class AiModuleStateSchema
{
    public static function make(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'module',
            description: 'Tracks conversational module progress, questions, and corrections during conversation flow.',
            properties: [
                new NumberSchema(
                    name: 'currentModule',
                    description: 'The current module number being executed in the conversation.'
                ),
                new ArraySchema(
                    name: 'questionsAsked',
                    description: 'List of question identifiers or text prompts that have been asked.',
                    items: new StringSchema('question', 'Single question identifier or text')
                ),
                new ArraySchema(
                    name: 'responsesReceived',
                    description: 'List of user responses, matched by index to questionsAsked.',
                    items: new StringSchema('response', 'User response text or normalized data value')
                ),
                new NumberSchema(
                    name: 'correctionsMade',
                    description: 'Number of corrections the user has made during the session.'
                ),
                new NumberSchema(
                    name: 'moduleProgress',
                    description: 'Track percentages or status objects. 0.0 ~ 1.0 for progress, 1.0 = completed',
                ),
            ],
            requiredFields: ['currentModule', 'questionsAsked', 'responsesReceived', 'correctionsMade', 'moduleProgress']
        );
    }
}
