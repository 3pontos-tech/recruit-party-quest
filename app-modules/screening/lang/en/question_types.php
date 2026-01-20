<?php

declare(strict_types=1);

return [
    'yes_no' => [
        'label' => 'Yes/No',
        'yes' => 'Yes',
        'no' => 'No',
    ],
    'text' => [
        'label' => 'Text',
        'settings' => [
            'max_length' => 'Maximum Length',
            'multiline' => 'Multi-line',
            'multiline_help' => 'Allow multiple lines of text (textarea)',
            'placeholder' => 'Placeholder',
            'placeholder_example' => 'Enter placeholder text...',
        ],
    ],
    'number' => [
        'label' => 'Number',
        'settings' => [
            'min' => 'Minimum Value',
            'max' => 'Maximum Value',
            'step' => 'Step',
            'prefix' => 'Prefix',
            'suffix' => 'Suffix',
        ],
    ],
    'single_choice' => [
        'label' => 'Single Choice',
        'select_placeholder' => 'Select an option...',
        'settings' => [
            'layout' => 'Layout',
            'layout_radio' => 'Radio Buttons',
            'layout_dropdown' => 'Dropdown',
            'choices' => 'Choices',
            'choice_value' => 'Value',
            'choice_label' => 'Label',
            'add_choice' => 'Add Choice',
        ],
    ],
    'multiple_choice' => [
        'label' => 'Multiple Choice',
        'select_between' => 'Select between :min and :max options',
        'select_min' => 'Select at least :min options',
        'select_max' => 'Select up to :max options',
        'settings' => [
            'min_selections' => 'Minimum Selections',
            'max_selections' => 'Maximum Selections',
            'no_limit' => 'No limit',
            'choices' => 'Choices',
            'choice_value' => 'Value',
            'choice_label' => 'Label',
            'add_choice' => 'Add Choice',
        ],
    ],
    'file_upload' => [
        'label' => 'File Upload',
        'click_to_upload' => 'Click to upload',
        'or_drag' => 'or drag and drop',
        'max_size_hint' => 'Max :size',
        'allowed_hint' => 'Allowed: :extensions',
        'max_files_hint' => 'Up to :count files',
        'settings' => [
            'max_size_kb' => 'Maximum File Size',
            'max_size_help' => '5120 KB = 5 MB',
            'max_files' => 'Maximum Files',
            'allowed_extensions' => 'Allowed Extensions',
            'extensions_placeholder' => 'Add extension...',
            'extensions_help' => 'Leave empty to allow all file types',
        ],
    ],
];
