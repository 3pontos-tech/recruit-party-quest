<?php

declare(strict_types=1);

return [
    'application_status' => [
        'new' => [
            'label' => 'New',
        ],
        'in_review' => [
            'label' => 'In Review',
        ],
        'in_progress' => [
            'label' => 'In Progress',
        ],
        'offer_extended' => [
            'label' => 'Offer Extended',
        ],
        'offer_accepted' => [
            'label' => 'Offer Accepted',
        ],
        'offer_declined' => [
            'label' => 'Offer Declined',
        ],
        'hired' => [
            'label' => 'Hired',
        ],
        'rejected' => [
            'label' => 'Rejected',
        ],
        'withdrawn' => [
            'label' => 'Withdrawn',
        ],
    ],
    'candidate_source' => [
        'linkedin' => [
            'label' => 'LinkedIn',
        ],
        'indeed' => [
            'label' => 'Indeed',
        ],
        'glassdoor' => [
            'label' => 'Glassdoor',
        ],
        'referral' => [
            'label' => 'Referral',
        ],
        'career_page' => [
            'label' => 'Career Page',
        ],
        'community' => [
            'label' => '3 Pontos Community',
        ],
        'three_dots' => [
            'label' => '3 Pontos',
        ],
        'other' => [
            'label' => 'Other',
        ],
    ],
    'rejection_reason_category' => [
        'qualifications' => [
            'label' => 'Qualifications',
        ],
        'experience' => [
            'label' => 'Experience',
        ],
        'culture_fit' => [
            'label' => 'Culture Fit',
        ],
        'compensation' => [
            'label' => 'Compensation',
        ],
        'location' => [
            'label' => 'Location',
        ],
        'availability' => [
            'label' => 'Availability',
        ],
        'position_filled' => [
            'label' => 'Position Filled',
        ],
        'other' => [
            'label' => 'Other',
        ],
        'screening_knockout' => [
            'label' => 'Automatic Screening',
        ],
    ],
    'application_status_group' => [
        'new' => ['label' => 'New'],
        'active' => ['label' => 'In progress'],
        'offer' => ['label' => 'Offer and hiring'],
        'closed' => ['label' => 'Closed'],
    ],
    'screening_verdict_filter' => [
        'all' => ['label' => 'All screening results'],
        'passed' => ['label' => 'Passed the knockout questions'],
        'failed' => ['label' => 'Failed the knockout questions'],
        'unanswered' => ['label' => 'No screening answers'],
    ],
    'seen_filter' => [
        'all' => ['label' => 'Seen and unseen'],
        'unseen' => ['label' => 'Unseen'],
        'seen' => ['label' => 'Already seen'],
    ],
    'application_list_sort' => [
        'attention' => ['label' => 'Attention'],
        'days_in_stage' => ['label' => 'Longest in stage'],
        'applied' => ['label' => 'Most recent application'],
        'name' => ['label' => 'Name'],
        'stage' => ['label' => 'Furthest stage'],
    ],
];
