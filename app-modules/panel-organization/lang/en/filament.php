<?php

declare(strict_types=1);

return [
    'tabs' => [
        'overview' => 'Overview',
        'experience' => 'Experience',
    ],

    'section' => [
        'quick_actions' => 'Quick Actions',
    ],

    'actions' => [
        'advance_stage' => [
            'label' => 'Advance Stage',
            'modal_heading' => 'Advance to Next Stage',
            'modal_description' => 'Are you sure you want to advance this candidate to the next recruitment stage?',
        ],
        'schedule_interview' => [
            'label' => 'Schedule Interview',
            'modal_heading' => 'Schedule Interview',
            'modal_description' => 'Schedule an interview appointment with the candidate.',
        ],
        'send_email' => [
            'label' => 'Send Email',
            'modal_heading' => 'Send Email to Candidate',
            'modal_description' => 'Send a personalized email to the candidate.',
        ],
        'add_comment' => [
            'label' => 'Add Internal Comment',
            'modal_heading' => 'Add Internal Comment',
            'modal_description' => 'Add a note that will only be visible to recruiters and administrators.',
        ],
        'reject_application' => [
            'label' => 'Reject Application',
            'modal_heading' => 'Reject Application',
            'modal_description' => 'This action cannot be undone. The candidate will be notified of the rejection.',
        ],
        'placeholder1' => 'Action 1',
        'placeholder2' => 'Action 2',
    ],

    'fields' => [
        'test_label' => 'Test',
        'test_placeholder' => 'Testing placeholder',
    ],

    'notifications' => [
        'ok_title' => 'Done',
        'ok_body' => 'Action completed successfully.',
    ],

    'tables' => [
        'last_movement' => 'Last Movement',
        'position' => 'Position',
        'published' => 'Published',
        'kanban' => 'Kanban',
    ],

    'group' => [
        'job' => 'Job',
        'job_no_title' => 'Untitled job',
        'job_description' => 'Company: :team • Department: :department',
    ],

    'kanban' => [
        'sections' => [
            'candidate_information' => 'Candidate Information',
            'application_comments' => 'Application Comments',
            'skills' => 'Skills',
        ],
        'skill_name' => 'Skill Name',
    ],

    'proficiency' => [
        1 => 'Beginner',
        2 => 'Basic',
        3 => 'Intermediate',
        4 => 'Advanced',
        5 => 'Expert',
    ],
];
