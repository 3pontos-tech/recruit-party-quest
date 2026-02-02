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
        'fields' => [
            'education' => 'Education',
            'institution' => 'Institution',
            'degree' => 'Degree',
            'field_of_study' => 'Field of Study',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'is_enrolled' => 'Currently Enrolled',
        ],
    ],

    'work_experience' => [
        'heading' => 'Work Experience',
        'description' => 'Manage your professional work history.',
        'submit' => 'Save Work Experience',
        'notify' => 'Work experience updated successfully.',
        'fields' => [
            'work_experiences' => 'Work Experiences',
            'company_name' => 'Company Name',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'is_currently_working_here' => 'Currently Working Here',
        ],
    ],

    'skills' => [
        'heading' => 'Skills',
        'description' => 'Manage your skills and proficiency levels.',
        'submit' => 'Save Skills',
        'notify' => 'Skills updated successfully.',
        'fields' => [
            'skills' => 'Skills',
            'skill' => 'Skill',
            'years_of_experience' => 'Years of Experience',
            'proficiency_level' => 'Proficiency Level',
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
