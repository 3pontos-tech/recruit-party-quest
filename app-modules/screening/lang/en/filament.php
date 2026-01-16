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
            'knockout_criteria' => 'Knockout Criteria',
            'responses_count' => 'Responses',
        ],
    ],
];
