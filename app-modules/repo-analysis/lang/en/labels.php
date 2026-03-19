<?php

declare(strict_types=1);

return [
    'status' => [
        'pending' => 'Pending',
        'analyzing' => 'Analyzing',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],
    'fields' => [
        'repository' => 'Repository',
        'language' => 'Language',
        'status' => 'Status',
        'analyzed_at' => 'Analyzed At',
        'private' => 'Private',
        'public' => 'Public',
    ],
    'actions' => [
        'view' => 'View',
        'new_analysis' => 'Analyze Repository',
        'analyze' => 'Analyze Repository',
        'back_to_list' => 'Back to analyses',
        'disabled' => [
            'no_github' => 'Connect your GitHub to analyze',
            'in_progress' => 'Analysis in progress...',
            'cooldown' => 'Available in :days day|Available in :days days',
        ],
    ],
    'notifications' => [
        'analysis_started' => 'Analysis started! We will notify you when it is complete.',
        'cooldown_active' => 'This repository can be re-analyzed from :date.',
        'analysis_completed' => 'Analysis of :repo is complete!',
        'analysis_failed' => 'The analysis failed. Please try again.',
        'analysis_completed_list' => 'An analysis has been completed.',
        'cooldown_redirect' => 'Your next analysis will be available in :days day.|Your next analysis will be available in :days days.',
        'github_unavailable' => 'Could not connect to GitHub. Please try again later.',
        'analysis_in_progress' => 'You already have an analysis in progress. Please wait for it to complete.',
    ],
    'page' => [
        'list' => [
            'heading' => 'Code Analysis',
            'empty_heading' => 'No analyses yet',
            'empty_description' => 'Analyze a repository to get technical feedback on your code.',
            'no_github' => [
                'heading' => 'Connect your GitHub account',
                'description' => 'To view and analyze your repositories, connect your GitHub account first.',
                'button' => 'Connect GitHub',
            ],
        ],
        'new' => [
            'heading' => 'Analyze Repository',
            'no_github' => [
                'heading' => 'Connect your GitHub account',
                'description' => 'To analyze a repository, you need to connect your GitHub account first.',
                'button' => 'Connect GitHub',
            ],
            'cooldown' => [
                'heading' => 'Cooldown active',
                'description' => 'This repository can be re-analyzed from :date.',
            ],
        ],
        'result' => [
            'analyzing' => 'Your repository is being analyzed. This may take a few minutes...',
            'failed' => 'The analysis failed. Please try again.',
            'summary' => 'Summary',
            'problems' => 'Problems found',
            'suggestions' => 'Improvement suggestions',
            'learning_topics' => 'Topics to study',
            'comparison' => 'Comparison with previous analysis',
            'improvements' => 'Improvements since last analysis',
            'unchanged_issues' => 'Issues still present',
            'regressions' => 'New issues or regressions',
        ],
    ],
    'impact_levels' => [
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
    ],
    'components' => [
        'repository_grid' => [
            'heading' => 'Repositories',
            'count_singular' => ':count recently updated repository',
            'count_plural' => ':count recently updated repositories',
            'empty' => [
                'heading' => 'No repositories found',
                'description' => 'Connect your GitHub account to get started',
            ],
        ],
        'repository_card' => [
            'analyze_button' => 'Analyze',
            'branch_label' => 'Default branch',
        ],
        'analysis_grid' => [
            'heading' => 'Analyses',
            'count_singular' => ':count analysis performed',
            'count_plural' => ':count analyses performed',
            'empty' => [
                'heading' => 'No analyses found',
                'description' => 'Select a repository to get started',
            ],
        ],
        'analysis_card' => [
            'processing' => 'Processing...',
            'view_button' => 'View',
        ],
        'analysis_header' => [
            'view_on_github' => 'View on GitHub',
            'back_to_list' => 'Back to list',
        ],
        'summary_section' => [
            'heading' => 'Summary',
        ],
        'highlights_section' => [
            'heading' => 'Highlights and Risks',
            'strengths_heading' => 'Strengths',
            'risks_heading' => 'Main Risks',
        ],
        'category_section' => [
            'problems_heading' => 'Problems',
            'problems_count' => 'Problems ( :count )',
            'suggestions_heading' => 'Suggestions',
            'suggestions_count' => 'Suggestions ( :count )',
            'study_topics_heading' => 'Learning Topics',
            'why_it_matters' => 'Why it matters',
        ],
        'detected_stack' => [
            'dependencies_heading' => 'Main Dependencies',
        ],
        'loading_state' => [
            'analyzing_heading' => 'Analyzing repository...',
            'analyzing_description' => 'This may take a few minutes. Please wait.',
            'back_button' => 'Back to list',
        ],
        'error_state' => [
            'heading' => 'Analysis failed',
            'description' => 'An error occurred while analyzing the repository. Please try again.',
            'back_button' => 'Back to list',
        ],
    ],
];
