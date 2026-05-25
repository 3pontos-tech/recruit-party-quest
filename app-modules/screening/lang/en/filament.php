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
        'sections' => [
            'question' => [
                'title' => 'Question',
                'description' => 'The text the candidate sees on the application form.',
            ],
            'answer' => [
                'title' => 'How it is answered',
                'description' => 'Set up the answer options for the chosen question type.',
            ],
            'knockout' => [
                'title' => 'Knockout filter (optional)',
                'description' => 'When enabled, the answer can automatically reject or advance the candidate, if the job requisition has automatic screening turned on.',
                'unsupported' => 'This question type does not support a knockout filter.',
            ],
        ],
        'fields' => [
            'question_text' => 'Question Text',
            'question_text_placeholder' => "e.g. Do you have a valid driver's license?",
            'question_type' => 'Question Type',
            'question_type_help' => 'Defines how the candidate answers: yes/no, number, single or multiple choice.',
            'display_order' => 'Display Order',
            'choices' => 'Choices',
            'choice_value' => 'Value',
            'choice_label' => 'Label',
            'is_required' => 'Required',
            'is_required_help' => 'The candidate cannot submit the application without answering this question.',
            'is_knockout' => 'Use this answer as a knockout filter',
            'is_knockout_help' => 'When on, define below which answer approves the candidate. A failing answer can auto-reject the candidate.',
            'knockout_criteria' => 'Knockout Criteria',
            'knockout_criteria_help' => 'Define expected values (e.g., expected: yes, minimum: 3)',
            'knockout_expected' => 'Approve the candidate if the answer is',
            'knockout_operator' => 'Approve if the number is',
            'knockout_value' => 'Reference value',
            'knockout_accepted' => 'Answers that approve',
            'knockout_accepted_multi_help' => 'The candidate passes if they select at least one of these.',
            'knockout_accepted_edit_warning' => 'If you rename or remove an option, review this criterion — outdated references are ignored when screening candidates.',
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
