<?php

declare(strict_types=1);

return [
    'profile_info' => [
        'heading' => 'Profile Information',
        'description' => 'Update your professional profile details and contact information.',
        'submit' => 'Save Profile',
        'notify' => 'Profile information updated successfully.',
        'fields' => [
            'avatar' => 'Profile Picture',
            'headline' => 'Headline',
            'summary' => 'Summary',
            'phone_number' => 'Phone Number',
        ],
        'placeholders' => [
            'headline' => 'e.g., Senior Software Engineer | Full-Stack Developer',
            'summary' => 'Write a brief professional summary highlighting your expertise, experience, and career goals...',
            'phone_number' => 'e.g., +1 (555) 123-4567',
        ],
    ],

    'preferences' => [
        'heading' => 'Preferences & Availability',
        'description' => 'Manage your job preferences, salary expectations, and availability.',
        'submit' => 'Save Preferences',
        'notify' => 'Preferences updated successfully.',
        'fields' => [
            'expected_salary' => 'Expected Salary',
            'expected_salary_currency' => 'Currency',
            'availability_date' => 'Availability Date',
            'willing_to_relocate' => 'Willing to Relocate',
            'is_open_to_remote' => 'Open to Remote Work',
            'experience_level' => 'Experience Level',
            'timezone' => 'Timezone',
            'preferred_language' => 'Preferred Language',
        ],
        'placeholders' => [
            'expected_salary' => 'e.g., 80000',
            'expected_salary_currency' => 'Select currency...',
            'experience_level' => 'Select your experience level...',
            'timezone' => 'Search or select timezone...',
            'preferred_language' => 'Select preferred language...',
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
                'pt_BR' => 'Portuguese (Brazil)',
                'en_US' => 'English (United States)',
            ],
        ],
    ],

    'education' => [
        'heading' => 'Education',
        'description' => 'Manage your educational background.',
        'submit' => 'Save Education',
        'notify' => 'Education updated successfully.',
        'add_education' => 'Add Education',
        'fields' => [
            'education' => 'Education',
            'institution' => 'Institution',
            'degree' => 'Degree',
            'field_of_study' => 'Field of Study',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'is_enrolled' => 'Currently Enrolled',
        ],
        'placeholders' => [
            'institution' => 'e.g., University of São Paulo',
            'degree' => 'e.g., Bachelor of Computer Science',
            'field_of_study' => 'e.g., Software Engineering',
        ],
    ],

    'work_experience' => [
        'heading' => 'Work Experience',
        'description' => 'Manage your professional work history.',
        'submit' => 'Save Work Experience',
        'notify' => 'Work experience updated successfully.',
        'add_work_experience' => 'Add Work Experience',
        'fields' => [
            'work_experiences' => 'Work Experiences',
            'company_name' => 'Company Name',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'is_currently_working_here' => 'Currently Working Here',
        ],
        'placeholders' => [
            'company_name' => 'e.g., Google, Microsoft, Startup Inc.',
            'description' => 'Describe your responsibilities, achievements, and key projects...',
        ],
    ],

    'skills' => [
        'heading' => 'Skills',
        'description' => 'Manage your skills and proficiency levels.',
        'submit' => 'Save Skills',
        'notify' => 'Skills updated successfully.',
        'add_skill' => 'Add Skill',
        'fields' => [
            'skills' => 'Skills',
            'skill' => 'Skill',
            'years_of_experience' => 'Years of Experience',
            'proficiency_level' => 'Proficiency Level',
        ],
        'placeholders' => [
            'skill' => 'Search or select a skill...',
            'years_suffix' => 'years',
            'proficiency_level' => 'Select proficiency level...',
        ],
        'options' => [
            'proficiency_levels' => [
                1 => 'Beginner',
                2 => 'Elementary',
                3 => 'Intermediate',
                4 => 'Advanced',
                5 => 'Expert',
            ],
        ],
    ],

    'resume_upload' => [
        'heading' => 'CV / Resume',
        'description' => 'Upload your CV to automatically add work experiences and education to your profile. A 3-day cooldown applies between uploads.',
        'upload_button' => 'Upload CV',
        'cv_file_label' => 'CV File',
        'cv_file_helper' => 'Upload your resume in PDF format (max 10 MB).',
        'notify_uploading' => 'Your CV is being processed by AI...',
        'notify_success' => 'Your profile has been updated from your CV.',
        'notify_error' => 'Something went wrong while processing your CV. Please try again later.',
        'cooldown_message' => 'You can upload a new CV in :days day(s).',
        'modal_title' => 'Before you upload',
        'modal_body' => 'The data extracted from your CV will be added to the following sections of your profile:',
        'modal_adds_experiences' => 'Work experiences',
        'modal_adds_education' => 'Education records',
        'modal_cancel' => 'Cancel',
        'modal_confirm' => 'Understood, upload CV',
    ],

    'links' => [
        'heading' => 'Social Links',
        'description' => 'Manage your social and professional links.',
        'submit' => 'Save Links',
        'notify' => 'Links updated successfully.',
        'add_link' => 'Add Link',
        'fields' => [
            'links' => 'Links',
            'url' => 'URL',
            'other_label' => 'Custom Label',
        ],
        'placeholders' => [
            'url' => 'https://...',
        ],
    ],
];
