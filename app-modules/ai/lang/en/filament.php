<?php

declare(strict_types=1);

return [
    'cluster' => [
        'navigation_label' => 'Artificial Intelligence',
        'breadcrumb' => 'AI',
    ],
    'resource' => [
        'ai_assistant' => [
            'label' => 'Custom Advisor',
            'plural_label' => 'Custom Advisors',
            'navigation_label' => 'Custom Advisors',
        ],
        'ai_message' => [
            'label' => 'AI Message',
            'plural_label' => 'AI Messages',
            'navigation_label' => 'AI Messages',
        ],
        'ai_thread' => [
            'label' => 'AI Thread',
            'plural_label' => 'AI Threads',
            'navigation_label' => 'AI Threads',
        ],
        'ai_thread_folder' => [
            'label' => 'AI Thread Folder',
            'plural_label' => 'AI Thread Folders',
            'navigation_label' => 'AI Thread Folders',
        ],
        'prompt' => [
            'label' => 'Prompt',
            'plural_label' => 'Prompts',
            'navigation_label' => 'Prompt Library',
        ],
        'prompt_type' => [
            'label' => 'Prompt Type',
            'plural_label' => 'Prompt Types',
            'navigation_label' => 'Prompt Types',
        ],
    ],
    'fields' => [
        'created_at' => 'Created Date',
        'updated_at' => 'Last Modified Date',
        'saved_at' => 'Saved Date',
        'locked_at' => 'Locked Date',
        'avatar' => 'Avatar',
        'owner' => 'Created By',
    ],
    'enums' => [
        'application' => [
            'personal_assistant' => [
                'label' => 'Custom Advisor',
            ],
            'test' => [
                'label' => 'Test',
            ],
        ],
        'prompt_tabs' => [
            'newest' => [
                'label' => 'Newest',
            ],
            'most_loved' => [
                'label' => 'Most Loved',
            ],
            'most_viewed' => [
                'label' => 'Most Viewed',
            ],
        ],
        'message_log_feature' => [
            'draft_with_ai' => [
                'label' => 'Draft With AI',
            ],
            'conversations' => [
                'label' => 'Conversations',
            ],
        ],
        'thread_share_target' => [
            'user' => [
                'label' => 'User',
            ],
            'team' => [
                'label' => 'Team',
            ],
        ],
        'reasoning_effort' => [
            'low' => [
                'label' => 'Low',
            ],
            'medium' => [
                'label' => 'Medium',
            ],
            'high' => [
                'label' => 'High',
            ],
        ],
        'model' => [
            'gpt-5-mini' => [
                'label' => 'GPT-5 Mini',
            ],
            'test' => [
                'label' => 'Test',
            ],
        ],
    ],
    'sections' => [
        'configure_ai_advisor' => [
            'title' => 'Configure AI Advisor',
            'description' => 'Design the capability of your advisor by including detailed instructions below.',
        ],
    ],
];
