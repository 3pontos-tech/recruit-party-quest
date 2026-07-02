<?php

declare(strict_types=1);

// config for Basement/BetterMails
use Basement\BetterMails\Core\Models\BetterEmail;
use Basement\BetterMails\Core\Models\BetterEmailAttachment;
use Basement\BetterMails\Core\Models\BetterEmailEvent;
use Basement\BetterMails\Resend\ResendDriver;

return [
    'mails' => [
        'models' => [
            'mail' => BetterEmail::class,
            'event' => BetterEmailEvent::class,
            'attachment' => BetterEmailAttachment::class,
        ],
        'database' => [
            'tables' => [
                'mails' => 'mails',
                'attachments' => 'mail_attachments',
                'events' => 'mail_events',
                'polymorph' => 'mailables',
            ],
            'pruning' => [
                'enabled' => false,
                'after' => 30, // days
            ],
        ],

        'headers' => [
            'key' => 'X-Better-Mails-Event-ID',
        ],

        'logging' => [
            'attachments' => [
                'enabled' => env('MAILS_LOGGING_ATTACHMENTS_ENABLED', true),
                'disk' => env('FILESYSTEM_DISK', 'local'),
                'root' => 'mails/attachments',
            ],
        ],
    ],
    'webhooks' => [
        'provider' => env('MAILS_WEBHOOK_PROVIDER', 'resend'),

        'drivers' => [
            'resend' => [
                'driver' => ResendDriver::class,
                'key_secret' => env('RESEND_WEBHOOK_SECRET'),
            ],
        ],
    ],
    'view_any' => true,
];
