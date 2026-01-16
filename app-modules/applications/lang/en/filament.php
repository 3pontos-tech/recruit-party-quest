<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Application',
        'plural_label' => 'Applications',
        'navigation_label' => 'Applications',
    ],
    'sections' => [
        'application_info' => 'Application Information',
        'cover_letter' => 'Cover Letter',
        'rejection' => 'Rejection Details',
        'offer' => 'Offer Details',
    ],
    'fields' => [
        'id' => 'ID',
        'tracking_code' => 'Tracking Code',
        'requisition' => 'Job Requisition',
        'candidate' => 'Candidate',
        'status' => 'Status',
        'source' => 'Source',
        'source_details' => 'Source Details',
        'current_stage' => 'Current Stage',
        'cover_letter' => 'Cover Letter',
        'evaluations_count' => 'Evaluations',
        'rejected_at' => 'Rejected At',
        'rejected_by' => 'Rejected By',
        'rejection_reason_category' => 'Rejection Category',
        'rejection_reason_details' => 'Rejection Details',
        'offer_extended_at' => 'Offer Extended At',
        'offer_extended_by' => 'Offer Extended By',
        'offer_amount' => 'Offer Amount',
        'offer_response_deadline' => 'Response Deadline',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'filters' => [
        'status' => 'Status',
        'source' => 'Source',
        'requisition' => 'Job Requisition',
        'current_stage' => 'Current Stage',
    ],
    'actions' => [
        'workflow' => [
            'label' => 'Actions',
        ],
        'bulk_move_to_stage' => [
            'label' => 'Move to Stage',
            'modal_heading' => 'Move Applications to Stage',
            'modal_submit' => 'Move Applications',
            'notification' => 'Moved :count applications to new stage.',
            'fields' => [
                'stage' => 'Stage',
                'notes' => 'Notes',
            ],
        ],
        'bulk_reject' => [
            'label' => 'Reject',
            'modal_heading' => 'Reject Applications',
            'modal_description' => 'Are you sure you want to reject these applications? This action cannot be undone.',
            'modal_submit' => 'Reject Applications',
            'notification' => 'Rejected :count applications.',
            'fields' => [
                'reason_category' => 'Rejection Reason',
                'reason_details' => 'Rejection Details',
            ],
        ],
        'extend_offer' => [
            'label' => 'Extend Offer',
            'modal_heading' => 'Extend Offer',
            'modal_description' => 'Send a job offer to this candidate.',
            'modal_submit' => 'Send Offer',
            'notification' => 'Offer extended to candidate.',
            'fields' => [
                'offer_amount' => 'Offer Amount',
                'response_deadline' => 'Response Deadline (days)',
            ],
        ],
        'mark_hired' => [
            'label' => 'Mark as Hired',
            'modal_heading' => 'Mark Application as Hired',
            'modal_description' => 'Mark this application as hired. The candidate status will be updated accordingly.',
            'modal_submit' => 'Mark as Hired',
            'notification' => 'Application marked as hired.',
        ],
        'move_to_stage' => [
            'label' => 'Move to Stage',
            'modal_heading' => 'Move Application to Stage',
            'modal_description' => 'Move this application to a different stage in the hiring pipeline.',
            'modal_submit' => 'Move to Stage',
            'notification' => 'Application moved to new stage.',
            'fields' => [
                'stage' => 'Stage',
                'notes' => 'Notes',
            ],
        ],
        'reject' => [
            'label' => 'Reject',
            'modal_heading' => 'Reject Application',
            'modal_description' => 'Are you sure you want to reject this application? This action cannot be undone.',
            'modal_submit' => 'Reject Application',
            'notification' => 'Application rejected.',
            'fields' => [
                'reason_category' => 'Rejection Reason',
                'reason_details' => 'Rejection Details',
            ],
        ],
        'withdraw' => [
            'label' => 'Withdraw',
            'modal_heading' => 'Withdraw Application',
            'modal_description' => 'Are you sure you want to withdraw this application? The candidate will be notified.',
            'modal_submit' => 'Withdraw Application',
            'notification' => 'Application withdrawn.',
        ],

    ],
];
