<?php

declare(strict_types=1);

return [
    'recruitment' => [
        'navigation_group' => 'Recruitment',
    ],
    'pages' => [
        'search_jobs' => [
            'header' => 'Search Jobs',
            'description' => 'Find your next opportunity from :count open positions',
            'search_placeholder' => 'Job title, keywords, or company',
            'search_button' => 'Search Jobs',
            'jobs_found' => 'jobs found',
            'no_jobs_found' => 'No jobs found',
            'no_jobs_description' => "Try adjusting your search or filters to find what you're looking for.",
            'clear_filters' => 'Clear all filters',
        ],
        'job_description' => [
            'no_posting' => 'Job posting details are currently unavailable.',
            'location_remote' => 'Remote',
            'diversity' => 'Diversity',
            'apply_button' => 'Apply for job',
            'applied_button' => 'Applied',
            'apply_modal_title' => 'Apply for :title',
            'about_this_job' => 'About this job',
            'responsibilities' => 'Responsibilities',
            'requirements' => 'Requirements',
            'desirable_skills' => 'Desirable skills',
            'benefits' => 'Benefits',
        ],
        'filters' => [
            'heading' => 'Filters',
        ],
    ],
    'stage_timeline' => [
        'application' => 'Application',
        'stage' => 'Stage',
        'by' => 'by',
        'empty' => 'No stage history available.',
    ],
    'widgets' => [
        'user_total_applications' => [
            'unique_views' => 'Unique views',
            'unique_views_description' => '32k increase',
            'bounce_rate' => 'Bounce rate',
            'bounce_rate_description' => '7% decrease',
        ],
    ],
];
