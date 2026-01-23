<?php

declare(strict_types=1);

return [
    'title' => 'Candidate Onboarding',
    'actions' => [
        'save_progress' => 'Save Progress',
        'continue_without_upload' => 'Continue without uploading',
        'finish' => 'Finish',
    ],
    'notifications' => [
        'progress_saved' => [
            'title' => 'Progress Saved',
            'message' => 'Your onboarding progress has been saved. You can continue later.',
        ],
        'consent_required' => [
            'title' => 'Consent Required',
            'message' => 'You must consent to data processing to complete onboarding.',
        ],
        'work_experience_required' => [
            'title' => 'Work Experience Required',
            'message' => 'Please add at least one work experience.',
        ],
        'education_required' => [
            'title' => 'Education Required',
            'message' => 'Please add at least one education entry.',
        ],
        'onboarding_complete' => [
            'title' => 'Onboarding Complete',
            'message' => 'Your profile has been set up successfully! You can now start applying for jobs.',
        ],
    ],
    'steps' => [
        'account' => [
            'label' => 'Account & Identity',
            'sections' => [
                'account_info' => 'Account Information',
            ],
            'options' => [
                'preferred_language' => [
                    'pt_BR' => 'Português (Brasil)',
                    'en_US' => 'English (United States)',
                ],
            ],
            'fields' => [
                'email' => 'Email',
                'timezone' => 'Timezone',
                'preferred_language' => 'Preferred Language',
                'data_consent' => 'I consent to the processing of my personal data',
                'data_consent_helper' => 'This is required to proceed with your application.',
            ],
        ],
        'cv' => [
            'label' => 'CV / Resume',
            'description' => 'Upload your curriculum vitae for parsing',
            'sections' => [
                'upload_cv' => 'Upload CV',
            ],
            'fields' => [
                'cv_file' => 'Upload CV',
                'cv_file_helper' => 'PDF or DOC files up to 10MB',
                'cv_file_uploading' => 'Wait while uploading your CV',
            ],
            'status_bar' => [
                'processing' => 'Processing',
                'finished' => 'Finished',
                'sent' => 'Sent',
            ],
        ],
        'profile' => [
            'label' => 'Professional Profile',
            'sections' => [
                'work_experience' => 'Work Experience',
                'education' => 'Education',
                'skills' => 'Skills',
            ],
            'fields' => [
                'work_experience' => 'Work Experience',
                'company_name' => 'Company Name',
                'description' => 'Description',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'is_currently_working_here' => 'Currently Working Here',
                'education' => 'Education',
                'institution' => 'Institution',
                'degree' => 'Degree',
                'field_of_study' => 'Field of Study',
                'is_enrolled' => 'Currently Enrolled',
                'skills' => 'Skills',
                'skills_helper' => 'At least 3 skills required',
            ],
        ],
        'preferences' => [
            'label' => 'Preferences & Availability',
            'sections' => [
                'compensation' => 'Compensation',
                'availability' => 'Availability',
                'job_interests' => 'Job Interests',
            ],
            'fields' => [
                'expected_salary' => 'Expected Salary',
                'currency' => 'Currency',
                'availability_date' => 'Availability Date',
                'availability_date_helper' => 'When are you available to start?',
                'willing_to_relocate' => 'Willing to Relocate',
                'is_open_to_remote' => 'Open to Remote Work',
                'experience_level' => 'Experience Level',
                'employment_type_interests' => 'Employment Types (comma-separated)',
                'employment_type_interests_placeholder' => 'full_time_employee, contractor, intern',
                'employment_type_interests_helper' => 'Full time employee, contractor, intern, etc.',
            ],
            'options' => [
                'experience_levels' => [
                    'intern' => 'Intern',
                    'entry_level' => 'Entry Level',
                    'mid_level' => 'Mid Level',
                    'senior' => 'Senior',
                    'lead' => 'Lead',
                    'principal' => 'Principal',
                ],
                'languages' => [
                    'en' => 'English',
                    'pt' => 'Português',
                    'es' => 'Español',
                ],
            ],
        ],
        'review' => [
            'label' => 'Review & Publish',
            'sections' => [
                'review_summary' => 'Review Summary',
            ],
            'fields' => [
                'email' => 'Email',
                'phone' => 'Phone',
                'headline' => 'Headline',
                'expected_salary' => 'Expected Salary',
                'confirm_submission' => 'I confirm that all information is accurate and complete',
            ],
        ],
    ],
];
