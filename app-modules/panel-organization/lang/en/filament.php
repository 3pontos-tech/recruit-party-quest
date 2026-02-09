<?php

declare(strict_types=1);

return [
    'tabs' => [
        'overview' => 'Overview',
        'experience' => 'Experience',
        'comments' => 'Comments',
    ],

    'section' => [
        'quick_actions' => 'Quick Actions',
    ],
    'applications' => [
        'resource' => [
            'navigation_label' => 'Applications',
            'label' => 'Application',
            'plural_label' => 'Applications',
        ],
    ],
    'job-requisitions' => [
        'resource' => [
            'navigation_label' => 'Job Requisitions',
            'label' => 'Job Requisition',
            'plural_label' => 'Job Requisitions',
        ],
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
        'generate_job_requisition' => [
            'label' => 'Generate with AI',
            'modal_heading' => 'Generate Job Requisition with AI',
            'modal_description' => 'Provide basic information and let AI generate a complete job requisition and posting based on your company profile.',
            'fields' => [
                'title' => 'Job Title',
                'description' => 'Technologies & Work Details',
                'description_helper' => 'List key technologies, tools, and important work details. AI will use this to generate the full description.',
                'experience_level' => 'Experience Level',
                'experience_level_description' => 'Required seniority for this position',
                'employment_type' => 'Employment Type',
                'work_arrangement' => 'Work Arrangement',
                'work_arrangement_description' => 'Where and how the employee will work',
                'priority' => 'Priority',
                'priority_description' => 'How fast we must close this position?',
            ],
            'success_title' => 'Job created successfully!',
            'success_body' => 'The AI has generated your job requisition and posting. Review and edit as needed before publishing.',
            'error_title' => 'Generation Failed',
        ],
    ],

    'notifications' => [
        'ok_title' => 'Done',
        'ok_body' => 'Action completed successfully.',
    ],

    'tables' => [
        'last_movement' => 'Last Movement',
        'position' => 'Position',
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

    'forms' => [
        'overall_rating' => 'Overall Rating',
        'scores' => 'Scores',
        'criteria_key_placeholder' => 'Criterion',
        'comments' => 'Comments',
        'comments_placeholder' => 'Enter your comments...',
        'recommendation' => 'Recommendation',
        'recommendation_placeholder' => 'Enter your recommendation...',
        'strengths' => 'Strengths',
        'strengths_placeholder' => 'Enter candidate strengths...',
        'concerns' => 'Concerns',
        'concerns_placeholder' => 'Enter any concerns...',
    ],

];
