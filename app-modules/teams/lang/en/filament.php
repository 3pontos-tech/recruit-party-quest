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
    ],
    'table' => [
        'slug_description' => 'slug: :slug',
    ],
    'relation_managers' => [
        'members' => [
            'title' => 'Members',
            'label' => 'member',
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
    ],
];
