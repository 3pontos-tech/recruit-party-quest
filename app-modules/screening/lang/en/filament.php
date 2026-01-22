<?php

declare(strict_types=1);

return [
    'relation_managers' => [
        'questions' => [
            'title' => 'Screening Questions',
            'label' => 'Question',
            'plural_label' => 'Questions',
        ],
    ],
    'question' => [
        'fields' => [
            'question_text' => 'Question Text',
            'question_type' => 'Question Type',
            'display_order' => 'Display Order',
            'choices' => 'Choices',
            'choice_value' => 'Value',
            'choice_label' => 'Label',
            'is_required' => 'Required',
            'is_knockout' => 'Knockout',
            'is_knockout_help' => 'Automatically disqualify candidates with wrong answers',
            'knockout_criteria' => 'Knockout Criteria',
            'knockout_criteria_help' => 'Define expected values (e.g., expected: yes, minimum: 3)',
            'add_choice' => 'Add Choice',
            'responses_count' => 'Responses',
        ],
    ],
    'form_schema' => [
        'questions' => [
            'label' => 'Screening Questions',
            'new_question' => 'New Question',
            'add_question' => 'Add Question',
        ],
    ],
];
