<?php

declare(strict_types=1);

return [
    'profile_info' => [
        'heading' => 'Profile Information',
        'description' => 'Update your professional profile details and contact information.',
        'submit' => 'Save Profile',
        'notify' => 'Profile information updated successfully.',
        'fields' => [
            'headline' => 'Headline',
            'summary' => 'Summary',
            'phone_number' => 'Phone Number',
            'linkedin_url' => 'LinkedIn URL',
            'portfolio_url' => 'Portfolio URL',
        ],
        'placeholders' => [
            'headline' => 'e.g., Senior Software Engineer | Full-Stack Developer',
            'summary' => 'Write a brief professional summary highlighting your expertise, experience, and career goals...',
            'phone_number' => 'e.g., +1 (555) 123-4567',
            'linkedin_url' => 'your-profile-name',
            'portfolio_url' => 'www.yourportfolio.com',
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
];
