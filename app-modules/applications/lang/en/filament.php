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
];
