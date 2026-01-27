<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Team',
        'plural_label' => 'Teams',
        'navigation_label' => 'Teams',
    ],
    'fields' => [
        'id' => 'ID',
        'name' => 'Name',
        'description' => 'Description',
        'slug' => 'Slug',
        'status' => 'Status',
        'owner' => 'Owner',
        'contact_email' => 'Contact Email',
        'members_count' => 'Members Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
        'email' => 'Email',
        'password' => 'Password',
    ],
    'table' => [
        'slug_description' => 'slug: :slug',
    ],
    'relation_managers' => [
        'members' => [
            'title' => 'Members',
            'label' => 'Member',
            'plural_label' => 'Members',
            'joined_at' => 'Joined At',
            'invite_action' => 'Invite Member',
            'invite_heading' => 'Invite New Member',
            'invite_description' => 'Create a new user and add them to this team.',
            'invite_success' => 'Invitation sent successfully!',
        ],
        'departments' => [
            'title' => 'Departments',
            'label' => 'Department',
            'plural_label' => 'Departments',
        ],
    ],
    'department' => [
        'label' => 'Department',
        'plural_label' => 'Departments',
        'navigation_label' => 'Departments',
        'fields' => [
            'id' => 'ID',
            'team' => 'Team',
            'name' => 'Name',
            'description' => 'Description',
            'head_user' => 'Head',
            'requisitions_count' => 'Requisitions',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ],
        'filters' => [
            'team' => 'Team',
        ],
        'create_action' => 'Create Department',
    ],
    'emails' => [
        'team_invitation' => [
            'subject' => 'Welcome to :team_name team!',
            'greeting' => 'Hello :name,',
            'introduction' => 'You have been invited to join the :team_name team.',
            'credentials_title' => 'Your Login Credentials',
            'email_label' => 'Email',
            'password_label' => 'Temporary Password',
            'instructions' => 'Please log in using these credentials. We recommend changing your password after your first login.',
            'login_button' => 'Log In Now',
            'forgot_password_button' => 'Forgot Your Password?',
            'footer' => ':team_name Team',
        ],
    ],
    'profile' => [
        'label' => 'Team Profile',
        'sections' => [
            'about' => 'About Your Company',
            'about_description' => 'Tell candidates about your company, culture, and what makes you unique.',
            'work_environment' => 'Work Environment',
            'work_environment_description' => 'Describe your work schedule and accessibility accommodations.',
            'team_links' => 'Team Links',
            'team_links_description' => "Add links to your company's social media profiles.",
        ],
        'fields' => [
            'about' => 'About',
            'work_schedule' => 'Work Schedule',
            'accessibility_accommodations' => 'Accessibility Accommodations',
            'is_disability_confident' => 'Disability Confident Employer',
        ],
    ],
];
