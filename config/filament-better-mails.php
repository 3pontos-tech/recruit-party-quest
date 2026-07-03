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

        /*
        | Only process webhook events whose sender matches one of these values.
        | Useful when several apps share the same provider account and every
        | endpoint receives events for all sending. Each value may be a full
        | email address ("noreply@flammabeneficios.com") or a bare domain
        | ("flammabeneficios.com"). Leave empty to process everything.
        | Comma separated.
        */
        'allowed_senders' => array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('MAILS_WEBHOOK_ALLOWED_SENDERS', ''))
        ))),

        'logging' => [
            'channel' => env('MAILS_WEBHOOK_LOG_CHANNEL'),
            'enabled' => env('MAILS_WEBHOOK_LOGGING_ENABLED', false),
        ],

        'drivers' => [
            'resend' => [
                'driver' => ResendDriver::class,
                'key_secret' => env('RESEND_WEBHOOK_SECRET'),
            ],
        ],
    ],
    'resource' => [
        'navigation_group' => 'Emails',
        'navigation_label' => 'Emails',
        'label' => 'Email',
        'slug' => 'mails',
        'navigation_icon' => 'heroicon-o-envelope',
    ],
    'view_any' => true,
];
