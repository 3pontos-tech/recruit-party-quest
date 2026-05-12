<?php

declare(strict_types=1);

use He4rt\Feedback\Models\Comment;
use He4rt\Users\User;
use Kirschbaum\Commentions\Listeners\SendUserMentionedNotification;
use Kirschbaum\Commentions\Notifications\UserMentionedInComment;
use Kirschbaum\Commentions\Policies\CommentPolicy;

return [
    /*
    |--------------------------------------------------------------------------
    | Table name configurations
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'comments' => 'comments',
        'comment_reactions' => 'comment_reactions',
        'comment_subscriptions' => 'comment_subscriptions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commenter model configuration
    |--------------------------------------------------------------------------
    */
    'commenter' => [
        'model' => User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment model configuration
    |--------------------------------------------------------------------------
    */
    'comment' => [
        'model' => Comment::class,
        'policy' => CommentPolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reactions
    |--------------------------------------------------------------------------
    */
    'reactions' => [
        'allowed' => ['👍', '❤️', '😂', '😮', '😢', '🤔'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscriptions
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        'dispatch_as_mention' => false,
        'show_subscribers' => false,
        'show_sidebar' => false,
        'auto_subscribe_on_comment' => false,
        'auto_subscribe_on_mention' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications (opt-in)
    |--------------------------------------------------------------------------
    |
    | Configure notification delivery when a user is mentioned in a comment.
    | Disabled by default; enable and choose the channels you want to use.
    |
    */
    'notifications' => [
        'mentions' => [
            // Disabled: this project uses its own SendMentionNotification listener to avoid duplicates
            'enabled' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_ENABLED', false),

            'channels' => explode(',', (string) env('COMMENTIONS_NOTIFICATIONS_MENTIONS_CHANNELS', 'mail')),

            'listener' => SendUserMentionedNotification::class,
            'notification' => UserMentionedInComment::class,

            'mail' => [
                'subject' => env('COMMENTIONS_NOTIFICATIONS_MENTIONS_MAIL_SUBJECT', 'You were mentioned in a comment'),
            ],
        ],
    ],
];
