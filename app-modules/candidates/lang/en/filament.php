<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Candidate',
        'plural_label' => 'Candidates',
        'navigation_label' => 'Candidates',
    ],
    'sections' => [
        'user_info' => 'User Information',
        'professional_info' => 'Professional Information',
        'availability' => 'Availability',
        'compensation' => 'Compensation Expectations',
        'links' => 'Links & Portfolio',
    ],
    'fields' => [
        'id' => 'ID',
        'user' => 'User',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'headline' => 'Headline',
        'summary' => 'Summary',
        'availability_date' => 'Available From',
        'is_willing_to_relocate' => 'Willing to Relocate',
        'is_open_to_remote' => 'Open to Remote',
        'expected_salary' => 'Expected Salary',
        'expected_salary_currency' => 'Currency',
        'linkedin_url' => 'LinkedIn URL',
        'portfolio_url' => 'Portfolio URL',
        'skills_count' => 'Skills',
        'applications_count' => 'Applications',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],
    'filters' => [
        'is_willing_to_relocate' => 'Willing to Relocate',
        'is_open_to_remote' => 'Open to Remote',
    ],
];
